<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\TimesheetGenerationFile;
use App\Services\TimesheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TimesheetController extends Controller
{
    public function index(): View
    {
        $defaultPeriodStart = now()->month <= 6
            ? now()->copy()->startOfYear()
            : now()->copy()->month(7)->startOfMonth();
        $defaultPeriodEnd = $defaultPeriodStart->copy()->addMonths(5)->endOfMonth();

        return view('timesheets.index', [
            'employees' => Employee::query()->where('is_active', true)->orderBy('display_name')->get(),
            'generations' => TimesheetGeneration::query()->latest()->limit(5)->get(),
            'csvRows' => session('timesheet_csv_rows', []),
            'csvPeriodStart' => $defaultPeriodStart->format('Y-m-d'),
            'csvPeriodEnd' => $defaultPeriodEnd->format('Y-m-d'),
            'csvZipLabel' => sprintf('Feuilles_de_temps_%s', $defaultPeriodStart->month === 1 ? 'S1_'.$defaultPeriodStart->year : 'S2_'.$defaultPeriodStart->year),
        ]);
    }

    public function uploadCsv(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'csv_file' => ['required', 'file', 'max:1024'],
        ]);

        try {
            $rows = $this->parseCsv($data['csv_file']->getRealPath());
        } catch (Throwable $exception) {
            return back()->withErrors(['csv_file' => $exception->getMessage()]);
        }

        session(['timesheet_csv_rows' => $rows]);

        return redirect()->route('timesheets.index')->with('status', count($rows).' ligne(s) CSV pretes a verifier.');
    }

    public function clearCsv(): RedirectResponse
    {
        session()->forget('timesheet_csv_rows');

        return redirect()->route('timesheets.index');
    }

    public function generate(Request $request, TimesheetService $service): RedirectResponse|StreamedResponse
    {
        if ($request->has('rows')) {
            $data = $request->validate([
                'rows' => ['required', 'array', 'min:1'],
                'rows.*.selected' => ['nullable', 'boolean'],
                'rows.*.employee_id' => ['required', 'exists:employees,id'],
                'rows.*.first_name' => ['nullable', 'string', 'max:80'],
                'rows.*.last_name' => ['nullable', 'string', 'max:80'],
                'rows.*.entity_name' => ['nullable', 'string', 'max:80'],
                'rows.*.country' => ['nullable', 'string', 'max:80'],
                'rows.*.function_title' => ['nullable', 'string', 'max:120'],
                'rows.*.role' => ['nullable', 'string', 'max:80'],
                'rows.*.funds' => ['nullable', 'string', 'max:40'],
                'rows.*.ipas_rate' => ['nullable', 'numeric', 'between:0,100'],
                'rows.*.catal_rate' => ['nullable', 'numeric', 'between:0,100'],
                'rows.*.ipde_rate' => ['nullable', 'numeric', 'between:0,100'],
                'rows.*.other_projects_rate' => ['nullable', 'numeric', 'between:0,100'],
                'rows.*.analytic_code' => ['nullable', 'string', 'max:120'],
                'rows.*.location' => ['nullable', 'string', 'max:120'],
                'rows.*.employee_signature_name' => ['nullable', 'string', 'max:120'],
                'rows.*.signatory_name' => ['nullable', 'string', 'max:120'],
                'rows.*.signature_title' => ['nullable', 'string', 'max:120'],
                'rows.*.comments_label' => ['nullable', 'string', 'max:80'],
                'rows.*.include_comments' => ['nullable', 'boolean'],
                'period_start' => ['required', 'date'],
                'period_end' => ['required', 'date', 'after_or_equal:period_start'],
                'zip_label' => ['nullable', 'string', 'max:120'],
                'excluded_signature_dates' => ['nullable', 'string'],
            ]);

            try {
                $generation = $service->generateRows($data['rows'], $request->user(), [
                    'period_start' => $data['period_start'],
                    'period_end' => $data['period_end'],
                    'zip_label' => $data['zip_label'] ?? null,
                    'excluded_signature_dates' => $data['excluded_signature_dates'] ?? null,
                ]);
                session()->forget('timesheet_csv_rows');
            } catch (Throwable $exception) {
                return back()->withInput()->withErrors(['generation' => $exception->getMessage()]);
            }

            if ($request->boolean('download_zip')) {
                return Storage::disk('local')->download($generation->zip_path, "Feuilles_de_temps_{$generation->period_label}.zip");
            }

            return redirect()->route('timesheets.result', $generation);
        }

        $data = $request->validate([
            'period_type' => ['required', 'in:month,quarter,semester,custom'],
            'start_month' => ['required', 'integer', 'between:1,12'],
            'end_month' => ['required', 'integer', 'between:1,12', 'gte:start_month'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['exists:employees,id'],
            'entity_label' => ['nullable', 'string', 'max:80'],
            'signature_date' => ['nullable', 'date'],
            'signatory_name' => ['nullable', 'string', 'max:120'],
            'comments_label' => ['nullable', 'string', 'max:80'],
            'include_comments' => ['nullable', 'boolean'],
        ], [
            'employee_ids.required' => 'Veuillez selectionner au moins un collaborateur.',
            'end_month.gte' => 'Le mois de fin doit etre posterieur ou egal au mois de debut.',
        ]);

        try {
            $generation = $service->generate($data, $request->user());
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['generation' => $exception->getMessage()]);
        }

        return redirect()->route('timesheets.result', $generation);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseCsv(string $path): array
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
        $employeeLookup = [];
        foreach ($employees as $employee) {
            foreach ([
                $employee->name(),
                trim($employee->first_name.' '.$employee->last_name),
                trim($employee->first_name.'_'.$employee->last_name),
                trim($employee->first_name.' '.$employee->last_name.' '.$employee->display_name),
            ] as $candidate) {
                if ($candidate !== '') {
                    $employeeLookup[$this->csvKey($candidate)] = $employee->id;
                }
            }
        }
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
            $employee = $this->matchEmployeeId(
                $employees,
                $employeeLookup,
                trim((string) ($this->csvValue($row, ['employee_id', 'id']) ?? '')),
                $firstName,
                $lastName,
                trim((string) ($this->csvValue($row, ['collaborateur', 'employee', 'name', 'display_name']) ?? '')),
            );

            if ($employee === '') {
                throw new RuntimeException('Une ligne CSV ne permet pas d identifier le collaborateur.');
            }

            /** @var Employee $matchedEmployee */
            $matchedEmployee = $employees[(int) $employee];
            $rows[] = [
                'selected' => 1,
                'employee_id' => (int) $employee,
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
     * @param  \Illuminate\Support\Collection<int, Employee>  $employees
     * @param  array<string, int>  $employeeLookup
     */
    private function matchEmployeeId($employees, array $employeeLookup, string $employee, string $firstName, string $lastName, string $name): string
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

    public function result(TimesheetGeneration $generation): View
    {
        return view('timesheets.result', [
            'generation' => $generation->load(['files.employee', 'user']),
        ]);
    }

    public function history(): View
    {
        return view('timesheets.history', [
            'generations' => TimesheetGeneration::query()->with('user')->latest()->paginate(20),
        ]);
    }

    public function downloadFile(TimesheetGenerationFile $file): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    public function downloadZip(TimesheetGeneration $generation): StreamedResponse
    {
        abort_unless($generation->zip_path && Storage::disk('local')->exists($generation->zip_path), 404);

        return Storage::disk('local')->download($generation->zip_path, "Feuilles_de_temps_{$generation->period_label}.zip");
    }
}
