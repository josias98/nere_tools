<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveHolidayController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'date', 'unique:leave_holidays,date'], 'name' => ['required', 'string', 'max:255']]);
        $holiday = LeaveHoliday::query()->create($data);
        AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'leave.holiday_created', 'auditable_type' => LeaveHoliday::class, 'auditable_id' => $holiday->id]);

        return back()->with('success', 'Jour férié ajouté.');
    }

    public function destroy(Request $request, LeaveHoliday $holiday): RedirectResponse
    {
        AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'leave.holiday_deleted', 'auditable_type' => LeaveHoliday::class, 'auditable_id' => $holiday->id, 'metadata' => ['date' => $holiday->date]]);
        $holiday->delete();

        return back()->with('success', 'Jour férié supprimé.');
    }
}
