<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\TimesheetGenerationFile;
use App\Services\TimesheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TimesheetController extends Controller
{
    public function index(): View
    {
        return view('timesheets.index', [
            'employees' => Employee::query()->where('is_active', true)->orderBy('display_name')->get(),
            'generations' => TimesheetGeneration::query()->latest()->limit(5)->get(),
        ]);
    }

    public function generate(Request $request, TimesheetService $service): RedirectResponse
    {
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
