<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveSetting;
use Carbon\Carbon;

class LeaveAccrualService
{
    /**
     * Calcule les droits acquis depuis une date de référence jusqu'à une date cible.
     */
    public function calculateAccruedDays(Employee $employee, Carbon $referenceDate, Carbon $targetDate): float
    {
        if (! $employee->leave_eligible) {
            return 0;
        }

        $accrualPolicy = LeaveSetting::where('key', 'accrual_policy')->value('value') ?? 'end_of_month';
        $monthlyAccrual = (float) (LeaveSetting::where('key', 'monthly_accrual_days')->value('value') ?? 2.5);

        $months = 0;

        if ($accrualPolicy === 'end_of_month') {
            $currentDate = $referenceDate->copy()->endOfMonth();
            $targetDateEnd = $targetDate->copy()->endOfDay();
            while ($currentDate->lessThanOrEqualTo($targetDateEnd)) {
                $months++;
                $currentDate->addMonthNoOverflow()->endOfMonth();
            }
        } else {
            $months = $referenceDate->diffInMonths($targetDate);
        }

        return $months * $monthlyAccrual;
    }
}
