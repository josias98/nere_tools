<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveApprovalReminderService;
use App\Services\Leaves\LeaveValidatorService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeaveReminderController extends Controller
{
    public function __invoke(Request $request, LeaveRequest $leaveRequest, LeaveValidatorService $validators, LeaveApprovalReminderService $reminders): RedirectResponse
    {
        $leaveRequest->load('employee');
        abort_unless($validators->userCanAccessDocument($request->user(), $leaveRequest), 403);
        $data = $request->validate([
            'approval_id' => ['required', 'integer'],
            'recipient' => ['required', 'email', 'max:254'],
        ]);

        try {
            $name = $reminders->sendManual($leaveRequest, (int) $data['approval_id'], $data['recipient']);
        } catch (DomainException $exception) {
            return back()->withErrors(['reminder' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Manual leave reminder failed.', ['exception' => $exception, 'leave_request_id' => $leaveRequest->id]);

            return back()->withErrors(['reminder' => 'La relance n’a pas pu être préparée. Réessayez dans un instant.']);
        }

        return redirect()->route('leaves.show', $leaveRequest->uuid)
            ->withFragment('relance')
            ->with('success', 'La relance par email de '.$name.' a été prise en compte.');
    }
}
