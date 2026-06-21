<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'period_start',
    'period_end',
    'year',
    'period_label',
    'generated_by_user_id',
    'employee_count',
    'pdf_count',
    'zip_path',
    'status',
    'error_message',
])]
class TimesheetGeneration extends Model
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function files(): HasMany
    {
        return $this->hasMany(TimesheetGenerationFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }
}
