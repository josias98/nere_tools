<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestApproval extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SKIPPED = 'skipped';

    protected $guarded = [];

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'À traiter',
            self::STATUS_APPROVED => 'Approuvée',
            self::STATUS_REJECTED => 'Rejetée',
            self::STATUS_SKIPPED => 'Non requise',
            default => $this->status,
        };
    }

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function validatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_user_id');
    }

    public function validatorEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validator_employee_id');
    }
}
