<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LeaveRequestWorkflowService
{
    protected LeaveBalanceService $balanceService;
    protected LeaveDayCountService $dayCountService;

    public function __construct(LeaveBalanceService $balanceService, LeaveDayCountService $dayCountService)
    {
        $this->balanceService = $balanceService;
        $this->dayCountService = $dayCountService;
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
        
        // TODO: Dispatch notification to validators
        
        return $request;
    }
}
