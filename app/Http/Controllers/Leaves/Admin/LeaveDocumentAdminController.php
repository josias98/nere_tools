<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeavePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveDocumentAdminController extends Controller
{
    public function __construct(private LeavePdfService $pdfService)
    {
    }

    public function revoke(Request $request, LeaveDocument $document): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->pdfService->revoke($document, $request->user(), $data['reason'] ?? null);
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'document' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Document revoque.');
    }

    public function regenerate(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $document = $this->pdfService->regenerate($leaveRequest, $request->user(), $data['reason'] ?? null);
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'document' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Document regenere : '.$document->document_reference.'.');
    }
}
