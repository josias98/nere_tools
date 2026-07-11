<?php

namespace App\Services\Leaves;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $request = DB::transaction(function () use ($employee, $data, $userId, $startDate, $endDate): LeaveRequest {
            $request = LeaveRequest::query()->create([
                'uuid' => Str::uuid(),
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_days' => $this->dayCountService->calculateDays($startDate, $endDate),
                'status' => 'pending_supervisor',
                'requester_comment' => $data['requester_comment'] ?? null,
                'submitted_at' => now(),
                'created_by_user_id' => $userId,
            ]);

            $this->createApprovalChain($request);
            $this->audit('leave.created', $request, $userId);

            return $request->fresh(['employee.department', 'leaveType', 'currentApproval']);
        });

        $this->notifications()->notifyCurrentStepValidators($request);

        return $request;
    }

    public function approve(LeaveRequest $request, User $reviewer, ?string $comment = null, bool $override = false): LeaveRequest
    {
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

            if (! $override && $request->requested_days > $balance['available_balance']) {
                throw new DomainException('Solde insuffisant pour approuver cette demande.');
            }

            $request->forceFill([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $reviewer->id,
                'reviewer_comment' => $comment,
                'balance_before' => $balance['available_balance'],
                'balance_after' => $balance['available_balance'] - $request->requested_days,
            ])->save();

            $this->audit('leave.approved', $request, $reviewer->id, ['step' => $approval->step_key]);
            $notify = 'final';

            return $request->fresh(['employee.department', 'leaveType', 'approvals.validatorUser.employee']);
        });

        if ($notify === 'next') {
            $this->notifications()->notifyCurrentStepValidators($request);
        } elseif ($notify === 'final') {
            $this->pdfs()->generate($request, $reviewer);
            $this->notifications()->notifyFinalApprovalRecipients($request->fresh(['employee', 'document', 'approvals.validatorUser.employee']));
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

        $this->notifications()->notifyRejectionRecipients($request);

        return $request;
    }

    private function createApprovalChain(LeaveRequest $request): void
    {
        foreach ($this->steps() as $step) {
            $request->approvals()->create($step + ['status' => LeaveRequestApproval::STATUS_PENDING]);
        }
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
