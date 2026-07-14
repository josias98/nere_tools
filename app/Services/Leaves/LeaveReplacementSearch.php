<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;

class LeaveReplacementSearch
{
    /**
     * @return Collection<int, Employee>
     */
    public function search(User $actor, string $term): Collection
    {
        $employee = $actor->employee;
        $search = '%'.addcslashes(trim($term), '%_').'%';

        return Employee::query()
            ->where('is_active', true)
            ->when($employee, fn ($query) => $query->whereKeyNot($employee->id))
            ->when(trim($term) !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('display_name', 'like', $search)
                    ->orWhere('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            }))
            ->orderBy('display_name')
            ->limit(8)
            ->get();
    }
}
