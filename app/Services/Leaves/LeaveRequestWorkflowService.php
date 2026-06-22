<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LeaveRequestWorkflowService
{
    protected LeaveBalanceService $balanceService;
    protected LeaveDayCountService $dayCountService;
    protected ?LeaveNotificationService $notificationService;
    protected ?LeavePdfService $pdfService;

    public function __construct(
        LeaveBalanceService $balanceService,
        LeaveDayCountService $dayCountService,
        ?LeaveNotificationService $notificationService = null,
        ?LeavePdfService $pdfService = null
    ) {
        $this->balanceService = $balanceService;
        $this->dayCountService = $dayCountService;
        $this->notificationService = $notificationService;
        $this->pdfService = $pdfService;
    }

    public function submitRequest(Employee $employee, array $data, int $userId): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        
        if ($endDate->isBefore($startDate)) {
            throw new \Exception("La date de fin ne peut pas être antérieure à la date de début.");
        }

        // Check overlaps
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'under_review', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->exists();

        if ($overlap) {
            throw new \Exception("Une demande de congé existe déjà sur cette période.");
        }

        $requestedDays = $this->dayCountService->calculateDays($startDate, $endDate);
        
        // MVP: don't strictly block here, just a UI warning, or maybe block?
        // "À la soumission : Alerter le demandeur si la demande dépasse son solde projeté"
        // Let's create it.
        
        $request = new LeaveRequest();
        $request->uuid = Str::uuid();
        $request->employee_id = $employee->id;
        $request->leave_type_id = $data['leave_type_id'];
        $request->start_date = $startDate;
        $request->end_date = $endDate;
        $request->requested_days = $requestedDays;
        $request->status = 'submitted';
        $request->requester_comment = $data['requester_comment'] ?? null;
        $request->submitted_at = Carbon::now();
        $request->created_by_user_id = $userId;
        
        $request->save();
        
        ($this->notificationService ?? app(LeaveNotificationService::class))->requestSubmitted($request);
        $this->audit('leave.created', $request, $userId);
        
        return $request;
    }

    public function approve(LeaveRequest $request, User $reviewer, ?string $comment = null, bool $override = false): LeaveRequest
    {
        if (! in_array($request->status, ['submitted', 'under_review'], true)) {
            throw new \Exception('Cette demande ne peut plus être approuvée.');
        }

        $balance = $this->balanceService->getBalance($request->employee);

        if (! $override && $request->requested_days > $balance['available_balance']) {
            throw new \Exception('Solde insuffisant pour approuver cette demande.');
        }

        $request->forceFill([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $reviewer->id,
            'reviewer_comment' => $comment,
            'balance_before' => $balance['available_balance'],
            'balance_after' => $balance['available_balance'] - $request->requested_days,
        ])->save();

        ($this->pdfService ?? app(LeavePdfService::class))->generate($request);
        ($this->notificationService ?? app(LeaveNotificationService::class))->requestDecided($request);
        $this->audit('leave.approved', $request, $reviewer->id);

        return $request;
    }

    public function reject(LeaveRequest $request, User $reviewer, string $comment): LeaveRequest
    {
        if (trim($comment) === '') {
            throw new \Exception('Le commentaire est obligatoire pour rejeter une demande.');
        }

        if (! in_array($request->status, ['submitted', 'under_review'], true)) {
            throw new \Exception('Cette demande ne peut plus être rejetée.');
        }

        $request->forceFill([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $reviewer->id,
            'reviewer_comment' => $comment,
        ])->save();

        ($this->notificationService ?? app(LeaveNotificationService::class))->requestDecided($request);
        $this->audit('leave.rejected', $request, $reviewer->id);

        return $request;
    }

    private function audit(string $action, LeaveRequest $request, int $userId): void
    {
        AuditLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id' => $request->id,
            'metadata' => [
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'requested_days' => $request->requested_days,
            ],
        ]);
    }
}
