<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use App\Services\Leaves\LeaveValidatorService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LeaveValidationController extends Controller
{
    public function __construct(
        private LeaveValidatorService $validators,
        private LeaveRequestWorkflowService $workflow,
        private LeaveBalanceService $balances
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->validators->userCanValidate($request->user()), 403);

        return view('leaves.validations.index', [
            'requests' => $this->validators->pendingRequestsFor($request->user())->paginate(15),
        ]);
    }

    public function show(Request $request, LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['employee.department', 'leaveType', 'document', 'approvals.validatorUser.employee', 'currentApproval']);
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

            return redirect()->route('leaves.validations.index')->with('success', 'Decision enregistree.');
        } catch (DomainException $exception) {
            $this->logDecisionFailure('leave.validation.approve_failed', $leaveRequest, $request, $exception);

            return redirect()->route('leaves.validations.index')->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logDecisionFailure('leave.validation.approve_failed', $leaveRequest, $request, $exception);

            return redirect()->route('leaves.validations.index')->with('error', "La décision n'a pas pu être enregistrée. Veuillez réessayer.");
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

            return redirect()->route('leaves.validations.index')->with('success', 'Demande rejetee.');
        } catch (DomainException $exception) {
            $this->logDecisionFailure('leave.validation.reject_failed', $leaveRequest, $request, $exception);

            return redirect()->route('leaves.validations.index')->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logDecisionFailure('leave.validation.reject_failed', $leaveRequest, $request, $exception);

            return redirect()->route('leaves.validations.index')->withInput()->with('error', "La décision n'a pas pu être enregistrée. Veuillez réessayer.");
        }
    }

    private function logDecisionFailure(string $action, LeaveRequest $leaveRequest, Request $request, Throwable $exception): void
    {
        Log::warning($action, [
            'leave_request_id' => $leaveRequest->id,
            'leave_request_uuid' => $leaveRequest->uuid,
            'user_id' => $request->user()?->id,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);

        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id' => $leaveRequest->id,
            'metadata' => [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'status' => $leaveRequest->fresh()?->status,
            ],
        ]);
    }
}
