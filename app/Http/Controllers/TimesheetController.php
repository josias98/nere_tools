<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\TimesheetGenerationFile;
use App\Models\User;
use App\Modules\Timesheets\Support\TimesheetCsvParser;
use App\Services\TimesheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function uploadCsv(Request $request, TimesheetCsvParser $parser): RedirectResponse
    {
        $data = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel', 'max:1024'],
        ]);

        try {
            $rows = $parser->parse($data['csv_file']->getRealPath());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['csv_file' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Timesheet CSV import failed.', ['exception' => $exception, 'user_id' => $request->user()?->id]);

            return back()->withErrors(['csv_file' => "Le fichier CSV n'a pas pu être traité. Veuillez vérifier son format."]);
        }

        session(['timesheet_csv_rows' => $rows]);
        AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'timesheet.csv_analyzed', 'metadata' => ['rows' => count($rows)]]);

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
            } catch (RuntimeException $exception) {
                return back()->withInput()->withErrors(['generation' => $exception->getMessage()]);
            } catch (Throwable $exception) {
                Log::error('Timesheet row generation failed.', ['exception' => $exception, 'user_id' => $request->user()?->id]);

                return back()->withInput()->withErrors(['generation' => 'La génération a échoué. Veuillez réessayer.']);
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
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['generation' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Timesheet generation failed.', ['exception' => $exception, 'user_id' => $request->user()?->id]);

            return back()->withInput()->withErrors(['generation' => 'La génération a échoué. Veuillez réessayer.']);
        }

        return redirect()->route('timesheets.result', $generation);
    }

    public function result(TimesheetGeneration $generation): View
    {
        Gate::authorize('view', $generation);

        return view('timesheets.result', [
            'generation' => $generation->load(['files.employee', 'user']),
        ]);
    }

    public function history(): View
    {
        $query = TimesheetGeneration::query()->with('user')->latest();
        if (request()->user()->role !== User::ROLE_ADMIN) {
            $query->where('generated_by_user_id', request()->user()->id);
        }

        return view('timesheets.history', [
            'generations' => $query->paginate(20),
        ]);
    }

    public function downloadFile(TimesheetGenerationFile $file): StreamedResponse
    {
        Gate::authorize('view', $file->generation);
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);
        AuditLog::query()->create(['user_id' => request()->user()->id, 'action' => 'timesheet.file_downloaded', 'auditable_type' => TimesheetGenerationFile::class, 'auditable_id' => $file->id]);

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    public function downloadZip(TimesheetGeneration $generation): StreamedResponse
    {
        Gate::authorize('view', $generation);
        abort_unless($generation->zip_path && Storage::disk('local')->exists($generation->zip_path), 404);
        AuditLog::query()->create(['user_id' => request()->user()->id, 'action' => 'timesheet.zip_downloaded', 'auditable_type' => TimesheetGeneration::class, 'auditable_id' => $generation->id]);

        return Storage::disk('local')->download($generation->zip_path, "Feuilles_de_temps_{$generation->period_label}.zip");
    }
}
