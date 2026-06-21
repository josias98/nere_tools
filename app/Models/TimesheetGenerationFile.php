<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'timesheet_generation_id',
    'employee_id',
    'month',
    'year',
    'file_name',
    'file_path',
    'status',
])]
class TimesheetGenerationFile extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(TimesheetGeneration::class, 'timesheet_generation_id');
    }
}
