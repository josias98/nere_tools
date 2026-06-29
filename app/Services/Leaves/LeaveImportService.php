<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LeaveImportService
{
    /**
     * @return array<string, mixed>
     */
    public function importCsv(UploadedFile $file, string $referenceDate): array
    {
        $path = $file->getRealPath();
        $firstLine = (string) fgets(fopen($path, 'r'));
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $handle = fopen($path, 'r');
        $headers = $this->headers(fgetcsv($handle, 0, $delimiter) ?: []);
        $report = [
            'rows_read' => 0,
            'rows_imported' => 0,
            'rows_updated' => 0,
            'rows_skipped' => 0,
            'errors' => [],
            'read' => 0,
            'matched' => 0,
        ];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $report['rows_read']++;
            $report['read']++;
            $data = $this->row($headers, $row);
            $employeeResult = $this->matchEmployee($data);

            if (is_string($employeeResult)) {
                $this->skip($report, $employeeResult);
                continue;
            }

            $remaining = $this->number($data['solde_restant'] ?? $data['initial_remaining_days'] ?? null);

            if ($remaining === null) {
                $this->skip($report, 'Ligne '.$report['rows_read'].': solde_restant est obligatoire.');
                continue;
            }

            if ($remaining < 0 && ! config('leaves.allow_negative_balance_import')) {
                $this->skip($report, 'Ligne '.$report['rows_read'].': solde negatif refuse.');
                continue;
            }

            $payload = [
                'initial_acquired_days' => $this->number($data['total_acquis'] ?? $data['initial_acquired_days'] ?? $data['acquis'] ?? null) ?? 0,
                'initial_taken_days' => $this->number($data['total_pris'] ?? $data['initial_taken_days'] ?? $data['pris'] ?? null) ?? 0,
                'initial_remaining_days' => $remaining,
                'source' => 'csv_import',
                'notes' => $data['notes'] ?? $data['commentaire'] ?? $data['comments'] ?? null,
            ];

            $balance = LeaveBalance::query()->updateOrCreate(
                ['employee_id' => $employeeResult->id, 'reference_date' => $referenceDate],
                $payload,
            );

            if (! empty($data['date_embauche']) && ! $employeeResult->hire_date) {
                $employeeResult->update(['hire_date' => $this->date($data['date_embauche'])]);
            }

            $balance->wasRecentlyCreated ? $report['rows_imported']++ : $report['rows_updated']++;
            $report['matched']++;
        }

        fclose($handle);

        return $report;
    }

    /**
     * @return array<int, string>
     */
    private function headers(array $headers): array
    {
        return array_map(function ($header): string {
            return Str::of((string) $header)
                ->replace("\xEF\xBB\xBF", '')
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_')
                ->toString();
        }, $headers);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string> $row
     * @return array<string, string>
     */
    private function row(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        foreach ([
            'display_name' => ['nom', 'name', 'collaborateur'],
            'total_acquis' => ['initial_acquired_days', 'acquis'],
            'total_pris' => ['initial_taken_days', 'pris'],
            'solde_restant' => ['initial_remaining_days', 'solde', 'remaining_days'],
            'date_embauche' => ['hire_date'],
            'notes' => ['commentaire', 'comments'],
        ] as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if (! isset($data[$canonical]) && isset($data[$alias])) {
                    $data[$canonical] = $data[$alias];
                }
            }
        }

        return $data;
    }

    private function matchEmployee(array $data): Employee|string
    {
        if (! empty($data['employee_id'])) {
            $employee = Employee::query()->find($data['employee_id']);

            if ($employee) {
                return $employee;
            }
        }

        $email = Str::lower(trim($data['email'] ?? ''));

        if ($email !== '') {
            $employee = Employee::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($employee) {
                return $employee;
            }
        }

        $name = trim($data['display_name'] ?? '');

        if ($name === '' && $email === '' && empty($data['employee_id'])) {
            return 'Ligne sans identifiant employe.';
        }

        if ($name === '') {
            return 'Employe introuvable: '.($email ?: $data['employee_id']);
        }

        $normalized = $this->normalizeName($name);
        $matches = Employee::query()->get()->filter(fn (Employee $employee): bool => $this->normalizeName($employee->name()) === $normalized);

        return match ($matches->count()) {
            1 => $matches->first(),
            0 => 'Employe introuvable: '.$name,
            default => 'Nom ambigu: '.$name,
        };
    }

    private function number(?string $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return is_numeric(str_replace(',', '.', $value)) ? (float) str_replace(',', '.', $value) : null;
    }

    private function date(string $value): string
    {
        return Str::contains($value, '/')
            ? Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d')
            : Carbon::parse($value)->format('Y-m-d');
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();
    }

    private function skip(array &$report, string $message): void
    {
        $report['rows_skipped']++;
        $report['errors'][] = $message;
    }
}
