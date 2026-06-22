<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveDashboardController extends Controller
{
    protected LeaveBalanceService $balanceService;

    public function __construct(LeaveBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user->employee) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas associé à un profil employé.');
        }

        $balance = $this->balanceService->getBalance($user->employee);
        
        $recentRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $user->employee->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('leaves.index', compact('balance', 'recentRequests'));
    }
}
