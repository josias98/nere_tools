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
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $type = LeaveType::query()->with('rules')->findOrFail($data['leave_type_id']);
        $rule = $type->ruleAt($startDate);
        $configuration = $rule?->configuration ?? [];
        $unit = LeaveUnit::from($configuration['unit'] ?? $type->unit->value);
        $duration = $this->dayCountService->calculate($startDate, $endDate, $unit);

        $this->ensureSubmissionRules($employee, $type, $configuration, $startDate, $endDate, $unit, $duration);

        if ($endDate->isBefore($startDate)) {
            throw new DomainException('La date de fin ne peut pas être antérieure à la date de début.');
        }

        $overlap = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg', 'approved'])
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(fn ($query) => $query->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate));
            })
            ->exists();

        if ($overlap) {
            throw new DomainException('Une demande de congé existe déjà sur cette période.');
        }

        $request = DB::transaction(function () use ($employee, $data, $userId, $startDate, $endDate, $type, $duration, $rule, $configuration, $unit): LeaveRequest {
            $request = LeaveRequest::query()->create([
                'uuid' => Str::uuid(),
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_days' => $duration,
                'requested_duration' => $duration,
                'duration_unit' => $unit->value,
                'start_at' => $startDate,
                'end_at' => $endDate,
                'effective_return_at' => $this->dayCountService->effectiveReturn($endDate, $unit),
                'rule_snapshot' => [
                    'type' => array_merge($type->only(['id', 'name', 'slug', 'category', 'unit', 'is_paid', 'counts_against_balance', 'quota', 'maximum_duration', 'legal_reference']), $configuration),
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
        if ($employee->is_active === false || $employee->leave_eligible === false) {
            throw new DomainException("Ce collaborateur n'est pas éligible aux demandes de congé.");
        }

        if ($type->is_active === false) {
            throw new DomainException("Ce type de congé n'est plus actif.");
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

        $year = $startDate->year;
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $used = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg', 'approved'])
            ->whereDate('start_date', '<=', $yearEnd)
            ->whereDate('end_date', '>=', $yearStart)
            ->get()
            ->sum(function (LeaveRequest $request) use ($year, $type): float {
                $requestUnit = LeaveUnit::tryFrom($request->duration_unit)
                    ?? LeaveUnit::tryFrom($request->rule_snapshot['type']['unit'] ?? '')
                    ?? $type->unit;

                return $this->dayCountService->splitByYear($request->start_date, $request->end_date, $requestUnit)[$year] ?? 0;
            });
        $requestedThisYear = $this->dayCountService->splitByYear($startDate, $endDate, $unit)[$year] ?? 0;

        if ($used + $requestedThisYear > (float) $quota) {
            throw new DomainException('Le quota annuel de ce type de congé serait dépassé.');
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
