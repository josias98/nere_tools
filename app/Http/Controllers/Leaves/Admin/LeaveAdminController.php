<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveSetting;
use App\Models\LeaveValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveAdminController extends Controller
{
    public function index(): View
    {
        return view('leaves.admin.index', [
            'employees' => Employee::query()->with('department')->orderBy('display_name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'settings' => LeaveSetting::query()->orderBy('key')->get()->keyBy('key'),
            'validators' => LeaveValidator::query()->with(['employee', 'department', 'targetEmployee'])->latest()->get(),
        ]);
    }

    public function updateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'unique:employees,email,'.$employee->id],
            'hire_date' => ['nullable', 'date'],
            'leave_eligible' => ['nullable', 'boolean'],
        ]);

        $data['leave_eligible'] = $request->boolean('leave_eligible');
        $employee->update($data);

        return back()->with('success', 'Collaborateur mis à jour.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'monthly_accrual_days' => ['required', 'numeric', 'min:0'],
            'accrual_policy' => ['required', 'in:end_of_month,start_of_month,prorated'],
        ]);

        foreach ($data as $key => $value) {
            LeaveSetting::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
