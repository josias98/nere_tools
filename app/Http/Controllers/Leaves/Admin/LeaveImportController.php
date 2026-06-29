<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Services\Leaves\LeaveImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveImportController extends Controller
{
    public function __invoke(Request $request, LeaveImportService $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'reference_date' => ['required', 'date'],
        ]);

        return back()
            ->with('success', 'Import termine.')
            ->with('import_report', $importer->importCsv($data['file'], $data['reference_date']));
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo "employee_id,email,display_name,nom,date_embauche,total_acquis,total_pris,solde_restant,notes\n";
        }, 'modele-import-soldes-conges.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
