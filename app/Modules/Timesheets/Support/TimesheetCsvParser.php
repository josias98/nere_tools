<?php

namespace App\Modules\Timesheets\Support;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class TimesheetCsvParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Impossible de lire le fichier CSV.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            throw new RuntimeException('Le fichier CSV est vide.');
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $headers = array_map(fn (string $header): string => $this->csvKey($header), str_getcsv($firstLine, $delimiter));
        $employees = Employee::query()->where('is_active', true)->get()->keyBy('id');
        $employeeLookup = $this->employeeLookup($employees);
        $rows = [];

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($line, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = array_combine($headers, array_pad($line, count($headers), ''));
            if ($row === false) {
                continue;
            }

            $firstName = trim((string) ($this->csvValue($row, ['first_name', 'prenom']) ?? ''));
            $lastName = trim((string) ($this->csvValue($row, ['last_name', 'nom']) ?? ''));
            $employeeId = $this->matchEmployeeId(
                $employees,
                $employeeLookup,
                trim((string) ($this->csvValue($row, ['employee_id', 'id']) ?? '')),
                $firstName,
                $lastName,
                trim((string) ($this->csvValue($row, ['collaborateur', 'employee', 'name', 'display_name']) ?? '')),
            );

            if ($employeeId === '') {
                throw new RuntimeException('Une ligne CSV ne permet pas d identifier le collaborateur.');
            }

            /** @var Employee $matchedEmployee */
            $matchedEmployee = $employees[(int) $employeeId];
            $rows[] = [
                'selected' => 1,
                'employee_id' => (int) $employeeId,
                'first_name' => $firstName !== '' ? $firstName : $matchedEmployee->first_name,
                'last_name' => $lastName !== '' ? $lastName : $matchedEmployee->last_name,
                'entity_name' => (string) ($this->csvValue($row, ['entity_name', 'entity_label', 'entite']) ?? $matchedEmployee->entity),
                'country' => (string) ($this->csvValue($row, ['country', 'pays']) ?? ''),
                'function_title' => (string) ($this->csvValue($row, ['function_title', 'fonction', 'job_title']) ?? $matchedEmployee->job_title),
                'role' => (string) ($this->csvValue($row, ['role']) ?? ''),
                'funds' => (string) ($this->csvValue($row, ['funds', 'fonds']) ?? ''),
                'ipas_rate' => $this->csvPercent($this->csvValue($row, ['ipas', 'ipas_rate'])),
                'catal_rate' => $this->csvPercent($this->csvValue($row, ['catal', 'catal_rate'])) ?? $matchedEmployee->catal_rate,
                'ipde_rate' => $this->csvPercent($this->csvValue($row, ['ipde', 'ipde_rate'])) ?? $matchedEmployee->ipde_rate,
                'other_projects_rate' => $this->csvPercent($this->csvValue($row, ['autre_projets', 'other_projects', 'other_projects_rate'])),
                'analytic_code' => (string) ($this->csvValue($row, ['code_analytique', 'analytic_code']) ?? $matchedEmployee->analytic_code),
                'location' => (string) ($this->csvValue($row, ['lieu', 'location']) ?? $matchedEmployee->location),
                'employee_signature_name' => (string) ($this->csvValue($row, ['nom_signature', 'employee_signature_name']) ?? $matchedEmployee->name()),
                'signatory_name' => (string) ($this->csvValue($row, ['responsable_hierarchique', 'signatory_name', 'responsable', 'signataire']) ?? $matchedEmployee->signatory_name),
                'signature_title' => (string) ($this->csvValue($row, ['signature_droite_titre', 'signature_title']) ?? $matchedEmployee->signature_title),
                'comments_label' => (string) ($this->csvValue($row, ['comments_label', 'commentaires']) ?? 'Commentaires / Details'),
                'include_comments' => $this->csvBool($this->csvValue($row, ['include_comments', 'avec_commentaires'])),
            ];
        }

        fclose($handle);

        if ($rows === []) {
            throw new RuntimeException('Le fichier CSV ne contient aucune ligne exploitable.');
        }

        return $rows;
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @return array<string, int>
     */
    private function employeeLookup(Collection $employees): array
    {
        $lookup = [];

        foreach ($employees as $employee) {
            foreach ([
                $employee->name(),
                trim($employee->first_name.' '.$employee->last_name),
                trim($employee->first_name.'_'.$employee->last_name),
                trim($employee->first_name.' '.$employee->last_name.' '.$employee->display_name),
            ] as $candidate) {
                if ($candidate !== '') {
                    $lookup[$this->csvKey($candidate)] = $employee->id;
                }
            }
        }

        return $lookup;
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  array<string, int>  $employeeLookup
     */
    private function matchEmployeeId(Collection $employees, array $employeeLookup, string $employee, string $firstName, string $lastName, string $name): string
    {
        if ($employee !== '') {
            return $employee;
        }

        foreach ([$name, trim($firstName.' '.$lastName), trim($firstName.'_'.$lastName)] as $candidate) {
            $employeeId = (string) ($employeeLookup[$this->csvKey($candidate)] ?? '');
            if ($employeeId !== '') {
                return $employeeId;
            }
        }

        $firstNameKey = $this->csvKey($firstName);
        if ($firstNameKey === '') {
            return '';
        }

        $lastNameKey = $this->csvKey($lastName);
        $matches = $employees->filter(function (Employee $employee) use ($firstNameKey, $lastNameKey): bool {
            if ($this->csvKey($employee->first_name) !== $firstNameKey) {
                return false;
            }

            if ($lastNameKey === '') {
                return true;
            }

            $employeeLastName = $this->csvKey($employee->last_name);

            return $employeeLastName === $lastNameKey
                || Str::contains($employeeLastName, $lastNameKey);
        });

        return $matches->count() === 1 ? (string) $matches->first()->id : '';
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function csvValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function csvKey(string $value): string
    {
        return trim(Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '_'), '_');
    }

    private function csvBool(mixed $value): int
    {
        return in_array(Str::of((string) $value)->ascii()->lower()->trim()->toString(), ['', '1', 'oui', 'yes', 'true'], true) ? 1 : 0;
    }

    private function csvPercent(mixed $value): ?float
    {
        $text = trim(str_replace('%', '', (string) $value));
        if ($text === '') {
            return null;
        }

        return is_numeric($text) ? (float) $text : null;
    }
}
