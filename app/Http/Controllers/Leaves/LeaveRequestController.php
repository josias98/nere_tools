<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leaves\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveDayCountService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use App\Services\Leaves\LeaveValidatorService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeaveRequestController extends Controller
{
    protected LeaveRequestWorkflowService $workflowService;

    protected LeaveDayCountService $dayCountService;

    protected LeaveValidatorService $validatorService;

    public function __construct(
        LeaveRequestWorkflowService $workflowService,
        LeaveDayCountService $dayCountService,
        LeaveValidatorService $validatorService
    ) {
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

        return view('leaves.create');
    }

    public function store(StoreLeaveRequest $request)
    {
        $user = Auth::user();

        if (! $user->employee) {
            return redirect()->route('dashboard')->with('error', 'Profil employé manquant.');
        }

        try {
            $leaveRequest = $this->workflowService->submitRequest($user->employee, $request->validated(), $user->id);

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
