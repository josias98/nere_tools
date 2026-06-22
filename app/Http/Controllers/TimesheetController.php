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
        return view('timesheets.index', [
            'employees' => Employee::query()->where('is_active', true)->orderBy('display_name')->get(),
            'generations' => TimesheetGeneration::query()->latest()->limit(5)->get(),
            'csvRows' => session('timesheet_csv_rows', []),
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
                'rows.*.employee_id' => ['required', 'exists:employees,id'],
                'rows.*.year' => ['required', 'integer', 'between:2020,2100'],
                'rows.*.start_month' => ['required', 'integer', 'between:1,12'],
                'rows.*.end_month' => ['required', 'integer', 'between:1,12'],
                'rows.*.entity_label' => ['nullable', 'string', 'max:80'],
                'rows.*.signature_date' => ['nullable', 'date'],
                'rows.*.signatory_name' => ['nullable', 'string', 'max:120'],
                'rows.*.comments_label' => ['nullable', 'string', 'max:80'],
                'rows.*.include_comments' => ['nullable', 'boolean'],
            ]);

            try {
                $generation = $service->generateRows($data['rows'], $request->user());
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
        $employees = Employee::query()->where('is_active', true)->get()
            ->mapWithKeys(fn (Employee $employee): array => [$this->csvKey($employee->name()) => $employee->id]);
        $rows = [];

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($line, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = array_combine($headers, array_pad($line, count($headers), ''));
            if ($row === false) {
                continue;
            }

            $employee = trim((string) ($this->csvValue($row, ['employee_id', 'id']) ?? ''));
            if ($employee === '') {
                $name = $this->csvKey((string) ($this->csvValue($row, ['collaborateur', 'employee', 'nom', 'name', 'display_name']) ?? ''));
                $employee = (string) ($employees[$name] ?? '');
            }

            if ($employee === '') {
                throw new RuntimeException('Une ligne CSV ne permet pas d identifier le collaborateur.');
            }

            $rows[] = [
                'employee_id' => (int) $employee,
                'year' => (int) ($this->csvValue($row, ['year', 'annee']) ?: now()->year),
                'start_month' => (int) ($this->csvValue($row, ['start_month', 'mois_debut', 'debut']) ?: 1),
                'end_month' => (int) ($this->csvValue($row, ['end_month', 'mois_fin', 'fin']) ?: 6),
                'entity_label' => (string) ($this->csvValue($row, ['entity_label', 'entite']) ?? ''),
                'signature_date' => (string) ($this->csvValue($row, ['signature_date', 'date_signature']) ?? ''),
                'signatory_name' => (string) ($this->csvValue($row, ['signatory_name', 'responsable', 'signataire']) ?? ''),
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
