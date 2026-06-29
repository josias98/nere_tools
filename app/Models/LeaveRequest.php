<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'requested_days' => 'float',
            'balance_before' => 'float',
            'balance_after' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function document(): HasOne
    {
        return $this->hasOne(LeaveDocument::class)
            ->where('status', LeaveDocument::STATUS_ACTIVE)
            ->latestOfMany();
    }

    public function activeDocument(): HasOne
    {
        return $this->document();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LeaveDocument::class)->latest('id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveRequestApproval::class)->orderBy('step_order');
    }

    public function currentApproval(): HasOne
    {
        return $this->hasOne(LeaveRequestApproval::class)
            ->where('status', LeaveRequestApproval::STATUS_PENDING)
            ->oldestOfMany('step_order');
    }
}
