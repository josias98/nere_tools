<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\Leaves\LeaveExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeaveExportController extends Controller
{
    public function __invoke(Request $request, LeaveExportService $export): BinaryFileResponse
    {
        $filters = $request->only(['status', 'step', 'department_id', 'employee_id', 'leave_type_id', 'from', 'to']);
        AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'leave.exported', 'metadata' => ['filters' => $filters]]);

        return response()->download($export->generate($filters), 'conges-nere-'.now()->format('Y-m-d-His').'.xlsx')->deleteFileAfterSend();
    }
}
