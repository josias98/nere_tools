<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

class LeaveReportQuery
{
    public function requests(array $filters): Builder
    {
        return LeaveRequest::query()
            ->with(['employee.department', 'leaveType', 'currentApproval', 'document', 'approvals.validatorUser.employee'])
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['step'] ?? null, fn ($q, $value) => $q->where('status', 'pending_'.$value))
            ->when($filters['department_id'] ?? null, fn ($q, $value) => $q->whereHas('employee', fn ($employee) => $employee->where('department_id', $value)))
            ->when($filters['employee_id'] ?? null, fn ($q, $value) => $q->where('employee_id', $value))
            ->when($filters['leave_type_id'] ?? null, fn ($q, $value) => $q->where('leave_type_id', $value))
            ->when($filters['from'] ?? null, fn ($q, $value) => $q->whereDate('start_date', '>=', $value))
            ->when($filters['to'] ?? null, fn ($q, $value) => $q->whereDate('end_date', '<=', $value))
            ->latest('submitted_at');
    }

    public function employees(array $filters): Builder
    {
        return Employee::query()->with('department')->where('is_active', true)
            ->when($filters['department_id'] ?? null, fn ($q, $value) => $q->where('department_id', $value))
            ->when($filters['employee_id'] ?? null, fn ($q, $value) => $q->whereKey($value))
            ->orderBy('display_name');
    }
}
