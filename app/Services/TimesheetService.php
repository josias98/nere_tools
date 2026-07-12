<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\TimesheetGenerationFile;
use App\Models\User;
use App\Modules\Timesheets\Support\TimesheetArchiveBuilder;
use App\Modules\Timesheets\Support\TimesheetPdfRenderer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class TimesheetService
{
    public function __construct(
        private TimesheetPdfRenderer $pdfRenderer,
        private TimesheetArchiveBuilder $archiveBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function generate(array $data, User $user): TimesheetGeneration
    {
        $employees = Employee::query()
            ->whereIn('id', $data['employee_ids'])
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        if ($employees->isEmpty()) {
            throw new RuntimeException('Veuillez selectionner au moins un collaborateur actif.');
        }

        $year = (int) $data['year'];
        $startMonth = (int) $data['start_month'];
        $endMonth = (int) $data['end_month'];

        $generation = TimesheetGeneration::query()->create([
            'uuid' => (string) Str::uuid(),
            'period_start' => Carbon::create($year, $startMonth, 1),
            'period_end' => Carbon::create($year, $endMonth, 1)->endOfMonth(),
            'year' => $year,
            'period_label' => $this->periodLabel($year, $startMonth, $endMonth),
            'generated_by_user_id' => $user->id,
            'employee_count' => $employees->count(),
            'pdf_count' => 0,
            'status' => 'completed',
        ]);

        $periods = array_map(
            fn (int $month): array => ['year' => $year, 'month' => $month],
            range($startMonth, $endMonth)
        );
        foreach ($employees as $employee) {
            $this->createFile($generation, $employee, $periods, $data);
        }

        $generation->update([
            'pdf_count' => $generation->files()->count(),
            'zip_path' => $this->zip($generation),
        ]);

        return $generation->refresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function generateRows(array $rows, User $user, array $options = []): TimesheetGeneration
    {
        $rows = array_values(array_filter($rows, fn (array $row): bool => (bool) ($row['selected'] ?? true)));

        if ($rows === []) {
            throw new RuntimeException('Le fichier CSV ne contient aucune ligne exploitable.');
        }

        $employeeIds = collect($rows)->pluck('employee_id')->map(fn ($id): int => (int) $id)->unique()->values();
        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($employees->count() !== $employeeIds->count()) {
            throw new RuntimeException('Une ligne CSV cible un collaborateur introuvable ou inactif.');
        }

        $monthPairs = [];
        if (filled($options['period_start'] ?? null) && filled($options['period_end'] ?? null)) {
            $periodStart = Carbon::parse((string) $options['period_start'])->startOfDay();
            $periodEnd = Carbon::parse((string) $options['period_end'])->endOfDay();
            $monthPairs = $this->monthPairs($periodStart, $periodEnd);
        } else {
            $starts = [];
            $ends = [];
            foreach ($rows as $row) {
                if ((int) $row['end_month'] < (int) $row['start_month']) {
                    throw new RuntimeException('Une ligne CSV a un mois de fin anterieur au mois de debut.');
                }

                $starts[] = Carbon::create((int) $row['year'], (int) $row['start_month'], 1);
                $ends[] = Carbon::create((int) $row['year'], (int) $row['end_month'], 1)->endOfMonth();
            }

            $periodStart = collect($starts)->sort()->first();
            $periodEnd = collect($ends)->sortDesc()->first();
        }

        $generation = TimesheetGeneration::query()->create([
            'uuid' => (string) Str::uuid(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'year' => (int) $periodStart->year,
            'period_label' => trim((string) ($options['zip_label'] ?? '')) ?: 'CSV_'.$periodStart->format('Ym').'_'.$periodEnd->format('Ym'),
            'generated_by_user_id' => $user->id,
            'employee_count' => $employeeIds->count(),
            'pdf_count' => 0,
            'status' => 'completed',
        ]);

        $excludedDates = $this->excludedDates((string) ($options['excluded_signature_dates'] ?? ''));
        $rowsByEmployee = collect($rows)->keyBy(fn (array $row): int => (int) $row['employee_id'])->values();
        foreach ($rowsByEmployee as $row) {
            $employee = $employees[(int) $row['employee_id']];
            if ($monthPairs !== []) {
                $this->createFile($generation, $employee, $monthPairs, [...$row, 'excluded_signature_dates' => $excludedDates]);

                continue;
            }

            $periods = array_map(
                fn (int $month): array => ['year' => (int) $row['year'], 'month' => $month],
                range((int) $row['start_month'], (int) $row['end_month'])
            );
            $this->createFile($generation, $employee, $periods, [...$row, 'excluded_signature_dates' => $excludedDates]);
        }

        $generation->update([
            'pdf_count' => $generation->files()->count(),
            'zip_path' => $this->zip($generation),
        ]);

        return $generation->refresh();
    }

    /**
     * @return array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    public function weeks(int $year, int $month): array
    {
        return $this->pdfRenderer->weeks($year, $month);
    }

    public function signatureDate(int $year, int $month, array $excludedDates = []): Carbon
    {
        return $this->pdfRenderer->signatureDate($year, $month, $excludedDates);
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    public function columns(Employee $employee, array $options = []): array
    {
        $columns = $this->pdfRenderer->columns($employee, $options);
        $total = array_sum(array_column($columns, 'value'));
        if (abs($total - 100) > 0.01) {
            throw new RuntimeException("Le total analytique de {$employee->name()} doit etre egal a 100%.");
        }

        return $columns;
    }

    /**
     * @param  array<int, array{year: int, month: int}>  $periods
     * @param  array<string, mixed>  $options
     */
    private function createFile(TimesheetGeneration $generation, Employee $employee, array $periods, array $options): TimesheetGenerationFile
    {
        $first = $periods[0];
        $last = $periods[array_key_last($periods)];
        $safeName = Str::of($this->employeeDisplayName($employee, $options))->ascii()->replaceMatches('/[^A-Za-z0-9]+/', '_')->trim('_');
        $fileName = sprintf('Feuilles_de_temps_%d%02d_%d%02d_%s.pdf', $first['year'], $first['month'], $last['year'], $last['month'], $safeName);
        $path = sprintf('timesheets/%s/%s/%s', $generation->uuid, $safeName, $fileName);

        Storage::disk('local')->put($path, $this->pdfRenderer->render($employee, $periods, $options));

        return $generation->files()->create([
            'employee_id' => $employee->id,
            'month' => $first['month'],
            'year' => $first['year'],
            'file_name' => $fileName,
            'file_path' => $path,
            'status' => 'generated',
        ]);
    }

    private function zip(TimesheetGeneration $generation): string
    {
        return $this->archiveBuilder->build($generation);
    }

    /**
     * @return array<int, array{year: int, month: int}>
     */
    private function monthPairs(Carbon $periodStart, Carbon $periodEnd): array
    {
        $cursor = $periodStart->copy()->startOfMonth();
        $end = $periodEnd->copy()->startOfMonth();
        $months = [];

        while ($cursor->lte($end)) {
            $months[] = ['year' => (int) $cursor->year, 'month' => (int) $cursor->month];
            $cursor->addMonth();
        }

        return $months;
    }

    /**
     * @return array<int, string>
     */
    private function excludedDates(string $rawDates): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $rawDates) ?: [])
            ->map(fn (string $date): ?string => $this->normalizedDate($date))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizedDate(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            $parsed = Carbon::createFromFormat($format, $date);
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    private function employeeDisplayName(Employee $employee, array $options): string
    {
        $name = trim((string) ($options['employee_signature_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $composed = trim((string) ($options['first_name'] ?? '').' '.(string) ($options['last_name'] ?? ''));

        return $composed !== '' ? $composed : $employee->name();
    }

    private function periodLabel(int $year, int $startMonth, int $endMonth): string
    {
        if ($startMonth === 1 && $endMonth === 6) {
            return "S1 {$year}";
        }

        if ($startMonth === 7 && $endMonth === 12) {
            return "S2 {$year}";
        }

        return sprintf('%d_%02d_%02d', $year, $startMonth, $endMonth);
    }
}
