<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveValidator extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'notify_by_email' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function targetEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_employee_id');
    }
}
