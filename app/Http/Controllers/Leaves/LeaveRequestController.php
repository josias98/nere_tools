<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveDayCountService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    protected LeaveBalanceService $balanceService;
    protected LeaveRequestWorkflowService $workflowService;
    protected LeaveDayCountService $dayCountService;

    public function __construct(
        LeaveBalanceService $balanceService,
        LeaveRequestWorkflowService $workflowService,
        LeaveDayCountService $dayCountService
    ) {
        $this->balanceService = $balanceService;
        $this->workflowService = $workflowService;
        $this->dayCountService = $dayCountService;
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->employee) {
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
        
        try {
            $leaveRequest = $this->workflowService->submitRequest($user->employee, $request->all(), $user->id);
            return redirect()->route('leaves.show', $leaveRequest->uuid)->with('success', 'Votre demande a été soumise avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        
        if ($leaveRequest->employee_id !== $user->employee?->id && !$user->hasRole('admin')) {
            abort(403);
        }

        return view('leaves.show', compact('leaveRequest'));
    }
}
