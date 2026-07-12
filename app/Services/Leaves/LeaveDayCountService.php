<?php

namespace App\Services\Leaves;

use App\Enums\LeaveUnit;
use App\Models\LeaveHoliday;
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

    public function calculate(Carbon $start, Carbon $end, LeaveUnit $unit): float
    {
        if ($end->lt($start)) {
            throw new \DomainException('La fin doit être postérieure au début.');
        }

        return match ($unit) {
            LeaveUnit::CalendarDay => $this->calculateDays($start, $end),
            LeaveUnit::WorkingDay => $this->workingDays($start, $end),
            LeaveUnit::Hour => round($start->diffInMinutes($end) / 60, 2),
            LeaveUnit::Week => round($this->calculateDays($start, $end) / 7, 2),
            LeaveUnit::Month => round($this->calculateDays($start, $end) / 30, 2),
        };
    }

    public function effectiveReturn(Carbon $end, LeaveUnit $unit): Carbon
    {
        $return = $end->copy()->addDay()->startOfDay();
        if ($unit !== LeaveUnit::WorkingDay) {
            return $return;
        }
        $holidays = LeaveHoliday::query()->whereDate('date', '>=', $return)->whereDate('date', '<=', $return->copy()->addDays(14))->pluck('date')->map(fn ($date) => Carbon::parse($date)->toDateString())->flip();
        while ($return->isWeekend() || $holidays->has($return->toDateString())) {
            $return->addDay();
        }

        return $return;
    }

    public function splitByYear(Carbon $start, Carbon $end, LeaveUnit $unit): array
    {
        $parts = [];
        for ($year = $start->year; $year <= $end->year; $year++) {
            $from = $year === $start->year ? $start : Carbon::create($year, 1, 1)->startOfDay();
            $to = $year === $end->year ? $end : Carbon::create($year, 12, 31)->endOfDay();
            $parts[$year] = $this->calculate($from, $to, $unit);
        }

        return $parts;
    }

    private function workingDays(Carbon $start, Carbon $end): float
    {
        $days = 0;
        $holidays = LeaveHoliday::query()->whereDate('date', '>=', $start)->whereDate('date', '<=', $end)->pluck('date')->map(fn ($date) => Carbon::parse($date)->toDateString())->flip();
        for ($date = $start->copy()->startOfDay(); $date->lte($end); $date->addDay()) {
            if (! $date->isWeekend() && ! $holidays->has($date->toDateString())) {
                $days++;
            }
        }

        return $days;
    }
}
