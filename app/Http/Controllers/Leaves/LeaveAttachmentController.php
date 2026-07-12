<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveAttachment;
use App\Services\Leaves\LeaveValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveAttachmentController extends Controller
{
    public function __invoke(Request $request, LeaveAttachment $attachment, LeaveValidatorService $validators): StreamedResponse
    {
        $attachment->load('leaveRequest.employee');
        abort_unless($validators->userCanAccessDocument($request->user(), $attachment->leaveRequest), 403);
        AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'leave.attachment_viewed', 'auditable_type' => LeaveAttachment::class, 'auditable_id' => $attachment->id]);

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }
}
