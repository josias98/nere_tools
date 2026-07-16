<?php

namespace App\Services\Leaves;

use App\Enums\LeaveUnit;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\LeaveType;
use App\Models\NotificationLog;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LeaveRequestWorkflowService
{
    public function __construct(
        protected LeaveBalanceService $balanceService,
        protected LeaveDayCountService $dayCountService,
        protected ?LeaveNotificationService $notificationService = null,
        protected ?LeavePdfService $pdfService = null,
        protected ?LeaveValidatorService $validatorService = null,
    ) {}

    public function submitRequest(Employee $employee, array $data, int $userId): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date'].(isset($data['start_time']) ? ' '.$data['start_time'] : ''));
        $endDate = Carbon::parse($data['end_date'].(isset($data['end_time']) ? ' '.$data['end_time'] : ''));
        $type = LeaveType::query()->with('rules')->findOrFail($data['leave_type_id']);
        $rule = $type->ruleAt($startDate);
        $configuration = $rule?->configuration ?? [];
        $unit = LeaveUnit::from($configuration['unit'] ?? $type->unit->value);
        $duration = $this->dayCountService->calculate($startDate, $endDate, $unit);

        $request = Cache::lock('leave-submission-employee:'.$employee->id, 120)
            ->block(10, function () use ($employee, $data, $userId, $startDate, $endDate, $type, $duration, $rule, $configuration, $unit): LeaveRequest {
                $this->ensureSubmissionRules($employee, $type, $configuration, $startDate, $endDate, $unit, $duration);
                $this->ensureRenewalRules($employee, $type, $configuration, $data['renewal_of_request_id'] ?? null, $startDate);
                $this->ensureNoOverlap($employee, $startDate, $endDate, $unit);

                return DB::transaction(function () use ($employee, $data, $userId, $startDate, $endDate, $type, $duration, $rule, $configuration, $unit): LeaveRequest {
                    $request = LeaveRequest::query()->create([
                        'uuid' => Str::uuid(),
                        'employee_id' => $employee->id,
                        'leave_type_id' => $data['leave_type_id'],
                        'renewal_of_request_id' => $data['renewal_of_request_id'] ?? null,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'requested_days' => $duration,
                        'requested_duration' => $duration,
                        'duration_unit' => $unit->value,
                        'start_at' => $startDate,
                        'end_at' => $endDate,
                        'effective_return_at' => $this->dayCountService->effectiveReturn($endDate, $unit),
                        'rule_snapshot' => [
                            'type' => array_merge($type->only(['id', 'name', 'slug', 'category', 'unit', 'is_paid', 'counts_against_balance', 'quota', 'maximum_duration', 'maximum_renewals', 'legal_reference']), $configuration),
                            'rule_version' => $rule?->version,
                            'configuration' => $rule?->configuration ?? [],
                            'captured_at' => now()->toIso8601String(),
                        ],
                        'status' => 'pending_supervisor',
                        'requester_comment' => $data['requester_comment'] ?? null,
                        'submitted_at' => now(),
                        'created_by_user_id' => $userId,
                        'relationship' => $data['relationship'] ?? null,
                        'reason' => $data['reason'] ?? null,
                        'replacement_needed' => (bool) ($data['replacement_needed'] ?? false),
                        'replacement_employee_id' => $data['replacement_employee_id'] ?? null,
                        'location' => $data['location'] ?? null,
                        'contact' => $data['contact'] ?? null,
                        'salary_impact' => $data['salary_impact'] ?? null,
                    ]);

                    foreach ($data['attachments'] ?? [] as $attachment) {
                        $this->storeAttachment($request, $attachment, $userId);
                    }

                    $this->createApprovalChain($request);
                    $this->audit('leave.created', $request, $userId);

                    return $request->fresh(['employee.department', 'leaveType', 'currentApproval']);
                });
            });

        $this->notifySafely(
            $request,
            'leave.pending_'.($request->currentApproval?->step_key ?? 'supervisor'),
            $userId,
            fn () => $this->notifications()->notifyCurrentStepValidators($request),
        );

        return $request;
    }

    public function approve(LeaveRequest $request, User $reviewer, ?string $comment = null, bool $override = false): LeaveRequest
    {
        [$request, $notify] = Cache::lock('leave-approval-employee:'.$request->employee_id, 120)
            ->block(10, function () use ($request, $reviewer, $comment, $override): array {
                $notify = null;
                $request = DB::transaction(function () use ($request, $reviewer, $comment, $override, &$notify): LeaveRequest {
                    $request = LeaveRequest::query()
                        ->with(['employee.department', 'leaveType'])
                        ->lockForUpdate()
                        ->findOrFail($request->id);

                    $this->ensureApprovalChain($request);
                    $approval = $this->currentApproval($request);

                    if (! $approval) {
                        throw new DomainException('Cette demande ne peut plus etre approuvee.');
                    }

                    if (! $this->validators()->userCanValidateStep($reviewer, $request, $approval->step_key)) {
                        throw new DomainException("Vous n'etes pas autorise a valider cette etape.");
                    }

                    $approval->forceFill([
                        'status' => LeaveRequestApproval::STATUS_APPROVED,
                        'validator_user_id' => $reviewer->id,
                        'validator_employee_id' => $reviewer->employee?->id,
                        'comment' => $comment,
                        'decided_at' => now(),
                    ])->save();

                    $next = $request->approvals()
                        ->where('status', LeaveRequestApproval::STATUS_PENDING)
                        ->orderBy('step_order')
                        ->first();

                    if ($next) {
                        $request->forceFill(['status' => 'pending_'.$next->step_key])->save();
                        $this->audit('leave.step_approved', $request, $reviewer->id, ['step' => $approval->step_key]);
                        $notify = 'next';

                        return $request->fresh(['employee.department', 'leaveType', 'approvals.validatorUser.employee', 'currentApproval']);
                    }

                    $balance = $this->balanceService->getBalance($request->employee);
                    $countsAgainstBalance = (bool) ($request->rule_snapshot['type']['counts_against_balance'] ?? $request->leaveType->counts_against_balance);

                    if ($countsAgainstBalance && ! $override && $request->requested_days > $balance['available_balance']) {
                        throw new DomainException('Solde insuffisant pour approuver cette demande.');
                    }

                    $request->forceFill([
                        'status' => 'approved',
                        'reviewed_at' => now(),
                        'reviewed_by_user_id' => $reviewer->id,
                        'reviewer_comment' => $comment,
                        'balance_before' => $balance['available_balance'],
                        'balance_after' => $countsAgainstBalance ? $balance['available_balance'] - $request->requested_days : $balance['available_balance'],
                    ])->save();

                    $this->audit('leave.approved', $request, $reviewer->id, ['step' => $approval->step_key]);
                    $notify = 'final';

                    return $request->fresh(['employee.department', 'leaveType', 'approvals.validatorUser.employee']);
                });

                return [$request, $notify];
            });

        if ($notify === 'next') {
            $this->notifySafely(
                $request,
                'leave.pending_'.($request->currentApproval?->step_key ?? 'unknown'),
                $reviewer->id,
                fn () => $this->notifications()->notifyCurrentStepValidators($request),
            );
        } elseif ($notify === 'final') {
            $this->generatePdfSafely($request, $reviewer);
            $this->notifySafely(
                $request,
                'leave.approved',
                $reviewer->id,
                fn () => $this->notifications()->notifyFinalApprovalRecipients($request->fresh(['employee', 'document', 'approvals.validatorUser.employee'])),
            );
        }

        return $request;
    }

    public function reject(LeaveRequest $request, User $reviewer, string $comment): LeaveRequest
    {
        if (trim($comment) === '') {
            throw new DomainException('Le commentaire est obligatoire pour rejeter une demande.');
        }

        $request = DB::transaction(function () use ($request, $reviewer, $comment): LeaveRequest {
            $request = LeaveRequest::query()
                ->with(['employee.department', 'leaveType'])
                ->lockForUpdate()
                ->findOrFail($request->id);

            $this->ensureApprovalChain($request);
            $approval = $this->currentApproval($request);

            if (! $approval) {
                throw new DomainException('Cette demande ne peut plus etre rejetee.');
            }

            if (! $this->validators()->userCanValidateStep($reviewer, $request, $approval->step_key)) {
                throw new DomainException("Vous n'etes pas autorise a rejeter cette etape.");
            }

            $approval->forceFill([
                'status' => LeaveRequestApproval::STATUS_REJECTED,
                'validator_user_id' => $reviewer->id,
                'validator_employee_id' => $reviewer->employee?->id,
                'comment' => $comment,
                'decided_at' => now(),
            ])->save();

            $request->forceFill([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $reviewer->id,
                'reviewer_comment' => $comment,
            ])->save();

            $this->audit('leave.rejected', $request, $reviewer->id, ['step' => $approval->step_key]);

            return $request->fresh(['employee.department', 'leaveType', 'approvals.validatorUser.employee']);
        });

        $this->notifySafely(
            $request,
            'leave.rejected',
            $reviewer->id,
            fn () => $this->notifications()->notifyRejectionRecipients($request),
        );

        return $request;
    }

    private function createApprovalChain(LeaveRequest $request): void
    {
        foreach ($this->steps() as $step) {
            $request->approvals()->create($step + ['status' => LeaveRequestApproval::STATUS_PENDING]);
        }
    }

    private function generatePdfSafely(LeaveRequest $request, User $reviewer): void
    {
        try {
            $this->pdfs()->generate($request, $reviewer);
        } catch (Throwable $exception) {
            Log::error('Leave PDF generation failed after approval.', ['exception' => $exception, 'leave_request_id' => $request->id]);
            $this->audit('leave.pdf_generation_failed', $request, $reviewer->id, ['message' => Str::limit($exception->getMessage(), 500)]);
        }
    }

    private function notifySafely(LeaveRequest $request, string $event, int $userId, Closure $notify): void
    {
        try {
            $notify();
        } catch (Throwable $exception) {
            Log::error('Leave notification dispatch failed.', ['exception' => $exception, 'leave_request_id' => $request->id, 'event' => $event]);
            $notification = NotificationLog::query()
                ->where('related_type', LeaveRequest::class)
                ->where('related_id', $request->id)
                ->where('event', $event)
                ->latest('id')
                ->first() ?? new NotificationLog([
                    'event' => $event,
                    'related_type' => LeaveRequest::class,
                    'related_id' => $request->id,
                    'subject' => 'Notification de congé à relancer',
                ]);
            $notification->forceFill([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
                'failed_at' => now(),
            ])->save();
            $this->audit('leave.notification_failed', $request, $userId, ['event' => $event, 'message' => Str::limit($exception->getMessage(), 500)]);
        }
    }

    private function ensureSubmissionRules(
        Employee $employee,
        LeaveType $type,
        array $configuration,
        Carbon $startDate,
        Carbon $endDate,
        LeaveUnit $unit,
        float $duration,
    ): void {
        if ($duration <= 0) {
            throw new DomainException('La durée demandée doit être supérieure à zéro.');
        }

        if ($employee->is_active === false || $employee->leave_eligible === false) {
            throw new DomainException("Ce collaborateur n'est pas éligible aux demandes de congé.");
        }

        if ($type->is_active === false) {
            throw new DomainException("Ce type de congé n'est plus actif.");
        }

        $requesterScope = $configuration['requester_scope'] ?? $type->requester_scope;
        if (! in_array($requesterScope, ['employee', 'both'], true)) {
            throw new DomainException("Ce type de congé n'est pas disponible en libre-service.");
        }

        $this->ensureEligibilityRules($employee, $configuration['eligibility_rules'] ?? $type->eligibility_rules ?? []);

        $countsAgainstBalance = (bool) ($configuration['counts_against_balance'] ?? $type->counts_against_balance);
        if ($unit === LeaveUnit::Hour && $countsAgainstBalance) {
            throw new DomainException('Un congé horaire ne peut pas impacter un solde exprimé en jours sans règle de conversion.');
        }

        $maximum = $configuration['maximum_duration'] ?? $type->maximum_duration;
        if ($maximum !== null && $duration > (float) $maximum) {
            throw new DomainException('La durée demandée dépasse le maximum autorisé pour ce type de congé.');
        }

        $noticeHours = (int) ($configuration['notice_hours'] ?? $type->notice_hours ?? 0);
        if ($noticeHours > 0 && $startDate->lt(now()->addHours($noticeHours))) {
            throw new DomainException("Le délai de préavis requis pour ce type de congé n'est pas respecté.");
        }

        $quota = $configuration['quota'] ?? $type->quota;
        if ($quota === null) {
            return;
        }

        $requestedByYear = $this->dayCountService->splitByYear($startDate, $endDate, $unit);
        $existing = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg', 'approved'])
            ->whereDate('start_date', '<=', Carbon::create(max(array_keys($requestedByYear)), 12, 31))
            ->whereDate('end_date', '>=', Carbon::create(min(array_keys($requestedByYear)), 1, 1))
            ->get();

        foreach ($requestedByYear as $year => $requestedThisYear) {
            $used = $existing->sum(function (LeaveRequest $request) use ($year, $type): float {
                $requestUnit = LeaveUnit::tryFrom($request->duration_unit)
                    ?? LeaveUnit::tryFrom($request->rule_snapshot['type']['unit'] ?? '')
                    ?? $type->unit;
                $requestStart = $request->start_at ?? $request->start_date->copy()->startOfDay();
                $requestEnd = $request->end_at ?? $request->end_date->copy()->endOfDay();

                if ($requestUnit === LeaveUnit::Hour && $requestStart->year === $requestEnd->year) {
                    return $requestStart->year === $year
                        ? (float) ($request->requested_duration ?? $request->requested_days)
                        : 0;
                }

                return $this->dayCountService->splitByYear($requestStart, $requestEnd, $requestUnit)[$year] ?? 0;
            });

            if ($used + $requestedThisYear > (float) $quota) {
                throw new DomainException('Le quota annuel de ce type de congé serait dépassé.');
            }
        }
    }

    private function ensureNoOverlap(Employee $employee, Carbon $startDate, Carbon $endDate, LeaveUnit $unit): void
    {
        $overlap = LeaveRequest::query()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg', 'approved'])
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->get()
            ->contains(function (LeaveRequest $request) use ($startDate, $endDate, $unit): bool {
                $existingUnit = LeaveUnit::tryFrom($request->duration_unit)
                    ?? LeaveUnit::tryFrom($request->rule_snapshot['type']['unit'] ?? '')
                    ?? $request->leaveType?->unit;

                if ($unit !== LeaveUnit::Hour || $existingUnit !== LeaveUnit::Hour) {
                    return true;
                }

                if (! $request->start_at || ! $request->end_at) {
                    return true;
                }

                return $request->start_at->lt($endDate) && $request->end_at->gt($startDate);
            });

        if ($overlap) {
            throw new DomainException('Une demande de congé existe déjà sur cette période.');
        }
    }

    private function ensureEligibilityRules(Employee $employee, array $rules): void
    {
        $supported = ['minimum_service_months', 'allowed_entities', 'allowed_locations', 'allowed_job_titles'];
        if (array_diff(array_keys($rules), $supported) !== []) {
            throw new DomainException("Les règles d'éligibilité de ce type de congé contiennent un critère non pris en charge.");
        }

        $minimumServiceMonths = (int) ($rules['minimum_service_months'] ?? 0);
        if ($minimumServiceMonths > 0 && (! $employee->hire_date || $employee->hire_date->copy()->addMonths($minimumServiceMonths)->isFuture())) {
            throw new DomainException("L'ancienneté minimale requise pour ce type de congé n'est pas atteinte.");
        }

        foreach (['allowed_entities' => 'entity', 'allowed_locations' => 'location', 'allowed_job_titles' => 'job_title'] as $rule => $attribute) {
            $allowed = $rules[$rule] ?? [];
            if ($allowed !== [] && ! in_array($employee->{$attribute}, $allowed, true)) {
                throw new DomainException("Ce collaborateur ne satisfait pas les règles d'éligibilité de ce type de congé.");
            }
        }
    }

    private function ensureRenewalRules(Employee $employee, LeaveType $type, array $configuration, ?int $renewalOfId, Carbon $startDate): void
    {
        if (! $renewalOfId) {
            return;
        }

        $maximumRenewals = (int) ($configuration['maximum_renewals'] ?? $type->maximum_renewals ?? 0);
        if ($maximumRenewals < 1) {
            throw new DomainException("Ce type de congé n'autorise pas de renouvellement.");
        }

        $original = LeaveRequest::query()->findOrFail($renewalOfId);
        if ($original->employee_id !== $employee->id || $original->leave_type_id !== $type->id || $original->status !== 'approved') {
            throw new DomainException('La demande indiquée ne peut pas être renouvelée.');
        }

        $originalEnd = $original->end_at ?? $original->end_date->copy()->endOfDay();
        if (! $startDate->gt($originalEnd)) {
            throw new DomainException('Le renouvellement doit commencer après la fin de la demande précédente.');
        }

        $renewalCount = 1;
        while ($original->renewal_of_request_id) {
            $renewalCount++;
            $original = LeaveRequest::query()->findOrFail($original->renewal_of_request_id);
        }

        if ($renewalCount > $maximumRenewals) {
            throw new DomainException('Le nombre maximal de renouvellements est atteint.');
        }
    }

    private function storeAttachment(LeaveRequest $request, UploadedFile $file, int $userId): void
    {
        $path = $file->store('private/leaves/attachments/'.$request->uuid, 'local');
        $request->attachments()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'sha256' => hash_file('sha256', Storage::disk('local')->path($path)),
            'uploaded_by_user_id' => $userId,
        ]);
    }

    private function ensureApprovalChain(LeaveRequest $request): void
    {
        if ($request->approvals()->exists()) {
            return;
        }

        $this->createApprovalChain($request);
        $request->forceFill(['status' => 'pending_supervisor'])->save();
    }

    private function currentApproval(LeaveRequest $request): ?LeaveRequestApproval
    {
        return $request->approvals()
            ->where('status', LeaveRequestApproval::STATUS_PENDING)
            ->orderBy('step_order')
            ->first();
    }

    private function audit(string $action, LeaveRequest $request, int $userId, array $metadata = []): void
    {
        AuditLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id' => $request->id,
            'metadata' => array_merge([
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'requested_days' => $request->requested_days,
            ], $metadata),
        ]);
    }

    /**
     * @return array<int, array{step_order: int, step_key: string, step_label: string}>
     */
    private function steps(): array
    {
        return [
            ['step_order' => 1, 'step_key' => 'supervisor', 'step_label' => 'Superviseur'],
            ['step_order' => 2, 'step_key' => 'hr', 'step_label' => 'RH / Admin-Finance'],
            ['step_order' => 3, 'step_key' => 'dg', 'step_label' => 'DG / Direction'],
        ];
    }

    private function validators(): LeaveValidatorService
    {
        return $this->validatorService ?? app(LeaveValidatorService::class);
    }

    private function notifications(): LeaveNotificationService
    {
        return $this->notificationService ?? app(LeaveNotificationService::class);
    }

    private function pdfs(): LeavePdfService
    {
        return $this->pdfService ?? app(LeavePdfService::class);
    }
}
