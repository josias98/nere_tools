<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaveValidatorService
{
    public function userCanValidate(User $user, ?LeaveRequest $request = null): bool
    {
        if ($request) {
            $approval = $request->currentApproval ?: $request->approvals()
                ->where('status', LeaveRequestApproval::STATUS_PENDING)
                ->orderBy('step_order')
                ->first();

            return $approval
                ? $this->userCanValidateStep($user, $request, $approval->step_key)
                : false;
        }

        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCE, User::ROLE_DIRECTION])
            || LeaveValidator::query()
                ->where('employee_id', $user->employee?->id)
                ->where('is_active', true)
                ->exists();
    }

    public function pendingRequestsFor(User $user): Builder
    {
        $query = LeaveRequest::query()
            ->with(['employee.department', 'leaveType', 'currentApproval'])
            ->whereIn('status', ['pending_supervisor', 'pending_hr', 'pending_dg'])
            ->whereHas('approvals', fn (Builder $approval) => $approval->where('status', LeaveRequestApproval::STATUS_PENDING))
            ->latest('submitted_at');

        $employee = $user->employee;

        if (! $employee && ! $user->hasAnyRole([User::ROLE_FINANCE, User::ROLE_DIRECTION])) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(fn (Builder $request) => $this->stepScopeForUser($request, $user));
    }

    /**
     * @return Collection<int, LeaveValidator>
     */
    public function validatorsFor(LeaveRequest $request): Collection
    {
        return $this->validatorsForStep($request, $request->currentApproval?->step_key ?? 'supervisor');
    }

    /**
     * @return Collection<int, LeaveValidator>
     */
    public function validatorsForStep(LeaveRequest $request, string $stepKey): Collection
    {
        return LeaveValidator::query()
            ->with('employee')
            ->where('step_key', $stepKey)
            ->where('is_active', true)
            ->where(function (Builder $query) use ($request, $stepKey): void {
                $query->where('scope', 'global')
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'department')
                            ->where('department_id', $request->employee?->department_id);
                    })
                    ->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'employee')
                            ->where('target_employee_id', $request->employee_id);
                    });

                if ($stepKey === 'supervisor') {
                    $query->orWhere(function (Builder $query) use ($request): void {
                        $query->where('scope', 'department')
                            ->where('department_id', $request->employee?->department_id);
                    });
                }
            })
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function usersForStep(LeaveRequest $request, string $stepKey): Collection
    {
        $validatorEmails = $this->validatorsForStep($request, $stepKey)
            ->pluck('employee.email')
            ->filter()
            ->map(fn (string $email): string => strtolower($email));

        $roleUsers = match ($stepKey) {
            'hr' => User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_FINANCE])->where('is_active', true)->get(),
            'dg' => User::query()->where('role', User::ROLE_DIRECTION)->where('is_active', true)->get(),
            default => collect(),
        };

        $validatorUsers = $validatorEmails->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('email', $validatorEmails)
                ->where('is_active', true)
                ->get();

        return $roleUsers
            ->merge($validatorUsers)
            ->unique('email')
            ->values();
    }

    public function userCanValidateStep(User $user, LeaveRequest $request, string $stepKey): bool
    {
        if ($stepKey === 'hr' && $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCE])) {
            return true;
        }

        if ($stepKey === 'dg' && $user->hasRole(User::ROLE_DIRECTION)) {
            return true;
        }

        $employee = $user->employee;

        if (! $employee) {
            return false;
        }

        return $this->validatorsForStep($request, $stepKey)
            ->contains(fn (LeaveValidator $validator): bool => $validator->employee_id === $employee->id);
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

    private function stepScopeForUser(Builder $query, User $user): void
    {
        $employee = $user->employee;
        $hasScope = false;

        if ($employee) {
            $validators = LeaveValidator::query()
                ->where('employee_id', $employee->id)
                ->where('is_active', true)
                ->get();

            foreach ($validators as $validator) {
                $hasScope = true;
                $query->orWhere(function (Builder $request) use ($validator): void {
                    $request->where('status', 'pending_'.$validator->step_key);

                    if ($validator->scope === 'department') {
                        $request->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $validator->department_id));
                    } elseif ($validator->scope === 'employee') {
                        $request->where('employee_id', $validator->target_employee_id);
                    }
                });
            }
        }

        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCE])) {
            $hasScope = true;
            $query->orWhere('status', 'pending_hr');
        }

        if ($user->hasRole(User::ROLE_DIRECTION)) {
            $hasScope = true;
            $query->orWhere('status', 'pending_dg');
        }

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }
    }
}
