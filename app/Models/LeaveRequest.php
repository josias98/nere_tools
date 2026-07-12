<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveRequest extends Model
{
    protected $guarded = [];

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            'submitted' => 'Demande envoyée',
            'under_review' => 'En cours de validation',
            'pending_supervisor' => 'Validation du responsable',
            'pending_hr' => 'Validation RH',
            'pending_dg' => 'Validation de la direction',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            'cancelled' => 'Annulée',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

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
            'requested_duration' => 'float',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'effective_return_at' => 'datetime',
            'rule_snapshot' => 'array',
            'replacement_needed' => 'boolean',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeaveAttachment::class);
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
