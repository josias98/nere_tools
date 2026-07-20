<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leaves\StoreLeaveRequestForEmployee;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LeaveRequestController extends Controller
{
    public function create(LeaveBalanceService $balances): View
    {
        $employees = Employee::query()
            ->where('is_active', true)
            ->where('leave_eligible', true)
            ->orderBy('display_name')
            ->get();
        $employeeBalances = $employees->mapWithKeys(
            fn (Employee $employee): array => [(string) $employee->id => $balances->getBalance($employee)]
        );
        $selectedEmployeeId = (string) old('employee_id', $employees->first()?->id);

        return view('leaves.create', [
            'adminMode' => true,
            'employees' => $employees,
            'employeeBalances' => $employeeBalances,
            'balance' => $employeeBalances->get($selectedEmployeeId, ['projected_balance' => 0]),
            'leaveTypes' => LeaveType::query()->with('rules')->where('is_active', true)->get(),
            'renewalOf' => null,
        ]);
    }

    public function store(
        StoreLeaveRequestForEmployee $request,
        LeaveRequestWorkflowService $workflow,
    ): RedirectResponse {
        $employee = Employee::query()
            ->where('is_active', true)
            ->where('leave_eligible', true)
            ->findOrFail($request->integer('employee_id'));

        try {
            $leaveRequest = $workflow->submitRequestForEmployee($employee, $request->validated(), $request->user());

            return redirect()->route('leaves.show', $leaveRequest->uuid)
                ->with('success', 'La demande a été créée pour '.$employee->name().'.');
        } catch (DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Administrative leave request submission failed.', [
                'user_id' => $request->user()->id,
                'employee_id' => $employee->id,
                'exception' => $exception,
            ]);

            return back()->withInput()->with('error', "La demande n'a pas pu être enregistrée. Veuillez réessayer.");
        }
    }
}
