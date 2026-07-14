<?php

namespace App\Services\Leaves;

use App\Enums\LeaveUnit;
use App\Models\Employee;
use App\Models\LeaveHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use DomainException;

class LeaveRequestPreviewService
{
    public function __construct(
        private LeaveBalanceService $balances,
        private LeaveDayCountService $days,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function preview(Employee $employee, ?LeaveType $type, array $input): array
    {
        if (! $type || empty($input['start_date']) || empty($input['end_date'])) {
            return $this->empty();
        }

        try {
            $start = Carbon::parse($input['start_date'])->startOfDay();
            $end = Carbon::parse($input['end_date'])->startOfDay();
            $rule = $type->ruleAt($start);
            $configuration = $rule?->configuration ?? [];
            $unit = LeaveUnit::from($configuration['unit'] ?? $type->unit->value);
            $duration = $this->days->calculate($start, $end, $unit);
            $returnAt = $this->days->effectiveReturn($end, $unit);
            $balance = $this->balances->getBalance($employee, $end);
            $counts = (bool) ($configuration['counts_against_balance'] ?? $type->counts_against_balance);
            $before = (float) $balance['projected_balance'];
            $after = $counts ? $before - $duration : $before;

            return [
                'ready' => true,
                'duration' => $duration,
                'unit' => $unit->label(),
                'return_date' => $returnAt->toDateString(),
                'non_counted_days' => $this->nonCountedDays($start, $end, $unit),
                'balance_before' => $before,
                'balance_after' => $after,
                'counts_against_balance' => $counts,
                'quota_before' => $type->quota,
                'quota_after' => $type->quota !== null ? max(0, (float) $type->quota - $duration) : null,
                'overlaps' => $this->overlaps($employee, $start, $end),
                'warnings' => $this->warnings($type, $configuration, $duration, $start, $before, $after, $counts),
            ];
        } catch (DomainException|\ValueError $exception) {
            return $this->empty($exception->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function empty(?string $error = null): array
    {
        return [
            'ready' => false,
            'duration' => null,
            'unit' => null,
            'return_date' => null,
            'non_counted_days' => [],
            'balance_before' => null,
            'balance_after' => null,
            'counts_against_balance' => true,
            'quota_before' => null,
            'quota_after' => null,
            'overlaps' => [],
            'warnings' => $error ? [$error] : [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function nonCountedDays(Carbon $start, Carbon $end, LeaveUnit $unit): array
    {
        if ($unit !== LeaveUnit::WorkingDay) {
            return [];
        }

        $holidays = LeaveHoliday::query()
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->pluck('name', 'date')
            ->mapWithKeys(fn (string $name, string $date): array => [Carbon::parse($date)->toDateString() => $name]);

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                $days[] = $date->toDateString().' (week-end)';
            } elseif ($holidays->has($date->toDateString())) {
                $days[] = $date->toDateString().' ('.$holidays[$date->toDateString()].')';
            }
        }

        return $days;
    }

    /**
     * @return array<int, array{uuid: string, status: string, start_date: string, end_date: string}>
     */
    private function overlaps(Employee $employee, Carbon $start, Carbon $end): array
    {
        return LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'under_review', 'pending_supervisor', 'pending_hr', 'pending_dg', 'approved'])
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(fn ($query) => $query->where('start_date', '<=', $start)->where('end_date', '>=', $end));
            })
            ->latest('start_date')
            ->limit(5)
            ->get(['uuid', 'status', 'start_date', 'end_date'])
            ->map(fn (LeaveRequest $request): array => [
                'uuid' => $request->uuid,
                'status' => $request->statusLabel(),
                'start_date' => $request->start_date->toDateString(),
                'end_date' => $request->end_date->toDateString(),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function warnings(LeaveType $type, array $configuration, float $duration, Carbon $start, float $before, float $after, bool $counts): array
    {
        $warnings = [];
        $maximum = $configuration['maximum_duration'] ?? $configuration['maximum'] ?? $type->maximum_duration;
        $noticeHours = (int) ($configuration['notice_hours'] ?? $type->notice_hours ?? 0);

        if ($maximum !== null && $duration > (float) $maximum) {
            $warnings[] = 'La duree depasse le plafond configure de '.(float) $maximum.'.';
        }

        if ($noticeHours > 0 && now()->diffInHours($start, false) < $noticeHours) {
            $warnings[] = 'Le delai interne recommande est de '.$noticeHours.' heure(s).';
        }

        if ($counts && $after < 0) {
            $warnings[] = 'Cette demande depasse le solde projete de '.abs(round($after, 2)).' jour(s).';
        }

        if ($counts && $before <= 0) {
            $warnings[] = 'Aucun solde projete disponible avant cette demande.';
        }

        return $warnings;
    }
}
