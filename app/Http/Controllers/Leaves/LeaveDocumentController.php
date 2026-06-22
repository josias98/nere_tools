<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveDocument;
use App\Models\User;
use App\Services\Leaves\LeaveValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveDocumentController extends Controller
{
    public function __invoke(Request $request, LeaveDocument $document, LeaveValidatorService $validators)
    {
        $document->load('leaveRequest.employee');
        $user = $request->user();
        $isOwner = $document->leaveRequest->employee_id === $user->employee?->id;
        $canDownload = $isOwner || $user->hasRole(User::ROLE_ADMIN) || $validators->userCanValidate($user, $document->leaveRequest);

        abort_unless($canDownload, 403);
        abort_unless($document->local_path && Storage::disk('local')->exists($document->local_path), 404);

        return Storage::disk('local')->download($document->local_path, $document->file_name);
    }
}
