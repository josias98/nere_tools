<?php

namespace App\Services\Leaves;

use Carbon\Carbon;

class LeaveDayCountService
{
    /**
     * Calcule le nombre de jours calendaires entre deux dates (incluses).
     */
    public function calculateDays(Carbon $startDate, Carbon $endDate): float
    {
        return $startDate->diffInDays($endDate) + 1;
    }
}
