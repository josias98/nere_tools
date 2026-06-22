<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
