<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveDayCountService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use App\Services\Leaves\LeaveValidatorService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeaveRequestController extends Controller
{
    protected LeaveBalanceService $balanceService;

    protected LeaveRequestWorkflowService $workflowService;

    protected LeaveDayCountService $dayCountService;

    protected LeaveValidatorService $validatorService;

    public function __construct(
        LeaveBalanceService $balanceService,
        LeaveRequestWorkflowService $workflowService,
        LeaveDayCountService $dayCountService,
        LeaveValidatorService $validatorService
    ) {
        $this->balanceService = $balanceService;
        $this->workflowService = $workflowService;
        $this->dayCountService = $dayCountService;
        $this->validatorService = $validatorService;
    }

    public function create()
    {
        $user = Auth::user();
        if (! $user->employee) {
            return redirect()->route('dashboard')->with('error', 'Profil employé manquant.');
        }

        $balance = $this->balanceService->getBalance($user->employee);
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leaves.create', compact('balance', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'requester_comment' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (! $user->employee) {
            return redirect()->route('dashboard')->with('error', 'Profil employé manquant.');
        }

        try {
            $leaveRequest = $this->workflowService->submitRequest($user->employee, $request->all(), $user->id);

            return redirect()->route('leaves.show', $leaveRequest->uuid)->with('success', 'Votre demande a été soumise avec succès.');
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Leave request submission failed.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return back()->withInput()->with('error', "La demande n'a pas pu être enregistrée. Veuillez réessayer.");
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $leaveRequest->load(['employee.department', 'leaveType', 'reviewer', 'document', 'approvals.validatorUser.employee', 'currentApproval']);

        if (! $this->validatorService->userCanAccessDocument($user, $leaveRequest)) {
            abort(403);
        }

        return view('leaves.show', compact('leaveRequest'));
    }
}
