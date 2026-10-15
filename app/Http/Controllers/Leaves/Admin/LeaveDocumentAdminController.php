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
            'reason' => ['required', 'string', 'max:2000'],
        ], ['reason.required' => 'Indiquez le motif de la révocation.']);

        try {
            $this->pdfService->revoke($document, $request->user(), $data['reason']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'document' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Leave document revocation failed.', ['exception' => $exception, 'document_id' => $document->id]);

            return back()->withErrors(['document' => "Le document n'a pas pu être révoqué. Veuillez réessayer."]);
        }

        return redirect()->route('leaves.show', $document->leaveRequest->uuid)
            ->withFragment('attestation')
            ->with('success', 'Attestation révoquée. Vous pouvez annuler cette révocation ci-dessous en cas d’erreur.');
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

        return redirect()->route('leaves.show', $leaveRequest->uuid)
            ->withFragment('attestation')
            ->with('success', 'Nouvelle attestation créée : '.$document->document_reference.'.');
    }

    public function restore(Request $request, LeaveDocument $document): RedirectResponse
    {
        try {
            $this->pdfService->restore($document, $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['document' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Leave document restoration failed.', ['exception' => $exception, 'document_id' => $document->id]);

            return back()->withErrors(['document' => "La révocation n'a pas pu être annulée. Veuillez réessayer."]);
        }

        return redirect()->route('leaves.show', $document->leaveRequest->uuid)
            ->withFragment('attestation')
            ->with('success', 'Révocation annulée. La même attestation est à nouveau valide, avec son PDF et son QR code d’origine.');
    }
}
