<?php

namespace App\Http\Controllers\Leaves;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->employee) {
            return redirect()->route('dashboard')->with('error', 'Profil employé manquant.');
        }

        $query = LeaveRequest::where('employee_id', $user->employee->id)->orderBy('created_at', 'desc');

        if ($request->has('year') && $request->year) {
            $query->whereYear('start_date', $request->year);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15);

        return view('leaves.history', compact('requests'));
    }
}
