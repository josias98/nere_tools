<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use App\Services\Leaves\LeaveValidatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveValidationController extends Controller
{
    public function __construct(
        private LeaveValidatorService $validators,
        private LeaveRequestWorkflowService $workflow,
        private LeaveBalanceService $balances
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($this->validators->userCanValidate($request->user()), 403);

        return view('leaves.validations.index', [
            'requests' => $this->validators->pendingRequestsFor($request->user())->paginate(15),
        ]);
    }

    public function show(Request $request, LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['employee.department', 'leaveType', 'document']);
        abort_unless($this->validators->userCanValidate($request->user(), $leaveRequest), 403);

        return view('leaves.validations.show', [
            'leaveRequest' => $leaveRequest,
            'balance' => $this->balances->getBalance($leaveRequest->employee),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->load('employee');
        abort_unless($this->validators->userCanValidate($request->user(), $leaveRequest), 403);

        try {
            $this->workflow->approve($leaveRequest, $request->user(), $request->string('reviewer_comment')->toString());

            return redirect()->route('leaves.validations.show', $leaveRequest->uuid)->with('success', 'Demande approuvée et PDF généré.');
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $data = $request->validate([
            'reviewer_comment' => ['required', 'string'],
        ]);

        $leaveRequest->load('employee');
        abort_unless($this->validators->userCanValidate($request->user(), $leaveRequest), 403);

        try {
            $this->workflow->reject($leaveRequest, $request->user(), $data['reviewer_comment']);

            return redirect()->route('leaves.validations.show', $leaveRequest->uuid)->with('success', 'Demande rejetée.');
        } catch (\Exception $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }
}
