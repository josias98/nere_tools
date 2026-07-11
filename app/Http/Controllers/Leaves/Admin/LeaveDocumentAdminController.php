<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeavePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class LeaveDocumentAdminController extends Controller
{
    public function __construct(private LeavePdfService $pdfService) {}

    public function revoke(Request $request, LeaveDocument $document): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->pdfService->revoke($document, $request->user(), $data['reason'] ?? null);
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'document' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Leave document revocation failed.', ['exception' => $exception, 'document_id' => $document->id]);

            return back()->withErrors(['document' => "Le document n'a pas pu être révoqué. Veuillez réessayer."]);
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
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'document' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Leave document regeneration failed.', ['exception' => $exception, 'leave_request_id' => $leaveRequest->id]);

            return back()->withErrors(['document' => "Le document n'a pas pu être régénéré. Veuillez réessayer."]);
        }

        return back()->with('success', 'Document regenere : '.$document->document_reference.'.');
    }
}
