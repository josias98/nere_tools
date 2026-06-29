<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveBalanceService
{
    protected LeaveAccrualService $accrualService;

    public function __construct(LeaveAccrualService $accrualService)
    {
        $this->accrualService = $accrualService;
    }

    public function getBalance(Employee $employee, Carbon $targetDate = null): array
    {
        $targetDate = $targetDate ?? Carbon::now();
        
        $balanceRecord = LeaveBalance::where('employee_id', $employee->id)
            ->orderByDesc('reference_date')
            ->first();

        $referenceDate = $balanceRecord ? Carbon::parse($balanceRecord->reference_date) : ($employee->hire_date ? Carbon::parse($employee->hire_date) : Carbon::now()->startOfYear());
        $initialRemaining = $balanceRecord ? (float) $balanceRecord->initial_remaining_days : 0;

        $accruedSinceReference = $this->accrualService->calculateAccruedDays($employee, $referenceDate, $targetDate);

        $adjustments = 0; // Future: sum from leave_balance_adjustments

        $approvedDaysSinceReference = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '>=', $referenceDate)
            ->sum('requested_days');

        $availableBalance = $initialRemaining + $accruedSinceReference + $adjustments - $approvedDaysSinceReference;

        $pendingDays = (float) LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg'])
            ->sum('requested_days');

        $projectedBalance = $availableBalance - $pendingDays;

        return [
            'initial_remaining_days' => $initialRemaining,
            'accrued_since_reference' => $accruedSinceReference,
            'approved_days_since_reference' => $approvedDaysSinceReference,
            'adjustments' => $adjustments,
            'available_balance' => $availableBalance,
            'pending_days' => $pendingDays,
            'projected_balance' => $projectedBalance,
        ];
    }
}
