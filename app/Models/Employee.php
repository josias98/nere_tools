<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'first_name',
    'last_name',
    'display_name',
    'email',
    'department_id',
    'hire_date',
    'leave_eligible',
    'leave_reference_date',
    'entity',
    'location',
    'job_title',
    'analytic_code',
    'ipas_rate',
    'catal_rate',
    'ipde_rate',
    'is_active',
    'requires_other_projects',
    'signature_title',
    'signatory_name',
])]
class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'ipas_rate' => 'float',
            'catal_rate' => 'float',
            'ipde_rate' => 'float',
            'is_active' => 'boolean',
            'hire_date' => 'date',
            'leave_eligible' => 'boolean',
            'leave_reference_date' => 'date',
            'requires_other_projects' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function name(): string
    {
        return $this->display_name ?: trim($this->first_name.' '.$this->last_name);
    }
}
