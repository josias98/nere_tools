<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeaveValidatorController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'step_key' => ['required', 'in:supervisor,hr,dg'],
            'scope' => ['required', 'in:global,department,employee'],
            'department_id' => ['nullable', 'required_if:scope,department', 'exists:departments,id'],
            'target_employee_id' => ['nullable', 'required_if:scope,employee', 'exists:employees,id'],
            'notify_by_email' => ['nullable', 'boolean'],
        ]);
        unset($data['notify_by_email']);
        $data['department_id'] = $data['scope'] === 'department' ? $data['department_id'] : null;
        $data['target_employee_id'] = $data['scope'] === 'employee' ? $data['target_employee_id'] : null;

        LeaveValidator::query()->updateOrCreate(
            $data,
            [
                'notify_by_email' => $request->boolean('notify_by_email'),
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Validateur enregistré.');
    }

    public function update(Request $request, LeaveValidator $validator): RedirectResponse
    {
        $validator->update([
            'notify_by_email' => $request->boolean('notify_by_email'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Validateur mis à jour.');
    }

    public function destroy(LeaveValidator $validator): RedirectResponse
    {
        $validator->delete();

        return back()->with('success', 'Validateur supprimé.');
    }
}
