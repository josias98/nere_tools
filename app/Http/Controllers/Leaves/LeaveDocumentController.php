<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveDocument;
use App\Services\Leaves\LeaveValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveDocumentController extends Controller
{
    public function __invoke(Request $request, LeaveDocument $document, LeaveValidatorService $validators)
    {
        $document->load('leaveRequest.employee');
        $user = $request->user();
        $canDownload = $validators->userCanAccessDocument($user, $document->leaveRequest);
        $path = $document->path();

        abort_unless($canDownload, 403);
        abort_unless($path && Storage::disk($document->disk ?: 'local')->exists($path), 404);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'leave_document.viewed',
            'auditable_type' => LeaveDocument::class,
            'auditable_id' => $document->id,
            'metadata' => [
                'document_reference' => $document->document_reference,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return Storage::disk($document->disk ?: 'local')->download($path, $document->file_name);
    }
}
