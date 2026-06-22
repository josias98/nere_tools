<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveBalance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LeaveImportService
{
    /**
     * @return array{read: int, matched: int, errors: array<int, string>}
     */
    public function importCsv(UploadedFile $file, string $referenceDate): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = array_map(fn ($header) => Str::of((string) $header)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString(), fgetcsv($handle) ?: []);
        $report = ['read' => 0, 'matched' => 0, 'errors' => []];

        while (($row = fgetcsv($handle)) !== false) {
            $report['read']++;
            $data = array_combine($headers, $row) ?: [];
            $employee = $this->matchEmployee($data);

            if (! $employee) {
                $report['errors'][] = 'Ligne '.$report['read'].' non matchee: '.($data['nom'] ?? $data['name'] ?? $data['email'] ?? 'sans nom');
                continue;
            }

            if (! empty($data['date_embauche']) && ! $employee->hire_date) {
                $employee->update(['hire_date' => $data['date_embauche']]);
            }

            LeaveBalance::query()->updateOrCreate(
                ['employee_id' => $employee->id, 'reference_date' => $referenceDate],
                [
                    'initial_acquired_days' => (float) ($data['total_acquis'] ?? $data['initial_acquired_days'] ?? 0),
                    'initial_taken_days' => (float) ($data['total_pris'] ?? $data['initial_taken_days'] ?? 0),
                    'initial_remaining_days' => (float) ($data['solde_restant'] ?? $data['initial_remaining_days'] ?? 0),
                    'source' => 'csv_import',
                    'notes' => $data['notes'] ?? null,
                ]
            );

            $report['matched']++;
        }

        fclose($handle);

        return $report;
    }

    /**
     * @param array<string, string> $data
     */
    private function matchEmployee(array $data): ?Employee
    {
        $email = Str::lower(trim($data['email'] ?? ''));

        if ($email !== '') {
            $employee = Employee::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($employee) {
                return $employee;
            }
        }

        $name = trim($data['nom'] ?? $data['name'] ?? $data['display_name'] ?? '');

        if ($name === '') {
            return null;
        }

        return Employee::query()
            ->whereRaw('LOWER(display_name) = ?', [Str::lower($name)])
            ->first();
    }
}
