<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Services\Leaves\LeaveImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveImportController extends Controller
{
    public function __invoke(Request $request, LeaveImportService $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'reference_date' => ['required', 'date'],
        ]);

        return back()
            ->with('success', 'Import terminé.')
            ->with('import_report', $importer->importCsv($data['file'], $data['reference_date']));
    }
}
