<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaveValidatorService
{
    public function userCanValidate(User $user, ?LeaveRequest $request = null): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        $employee = $user->employee;

        if (! $employee) {
            return false;
        }

        $query = LeaveValidator::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true);

        if ($request) {
            $query->where(function (Builder $query) use ($request): void {
                $query->where('scope', 'global')
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'department')
                            ->where('department_id', $request->employee?->department_id);
                    })
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'employee')
                            ->where('target_employee_id', $request->employee_id);
                    });
            });
        }

        return $query->exists();
    }

    public function pendingRequestsFor(User $user): Builder
    {
        $query = LeaveRequest::query()
            ->with(['employee.department', 'leaveType'])
            ->whereIn('status', ['submitted', 'under_review'])
            ->latest('submitted_at');

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return $query;
        }

        $employee = $user->employee;

        if (! $employee) {
            return $query->whereRaw('1 = 0');
        }

        $validators = LeaveValidator::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();

        if ($validators->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($validators): void {
            foreach ($validators as $validator) {
                if ($validator->scope === 'global') {
                    $query->orWhereNotNull('id');
                } elseif ($validator->scope === 'department') {
                    $query->orWhereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $validator->department_id));
                } elseif ($validator->scope === 'employee') {
                    $query->orWhere('employee_id', $validator->target_employee_id);
                }
            }
        });
    }

    /**
     * @return Collection<int, LeaveValidator>
     */
    public function validatorsFor(LeaveRequest $request): Collection
    {
        return LeaveValidator::query()
            ->with('employee')
            ->where('is_active', true)
            ->where(function (Builder $query) use ($request): void {
                $query->where('scope', 'global')
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'department')
                            ->where('department_id', $request->employee?->department_id);
                    })
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'employee')
                            ->where('target_employee_id', $request->employee_id);
                    });
            })
            ->get();
    }

    public function userCanAccessDocument(User $user, LeaveRequest $request): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCE, User::ROLE_DIRECTION])) {
            return true;
        }

        if ($user->canAccessAdmin()) {
            return true;
        }

        if ($request->employee_id === $user->employee?->id) {
            return true;
        }

        return $this->userCanValidate($user, $request);
    }
}
