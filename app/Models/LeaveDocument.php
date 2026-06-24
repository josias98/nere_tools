<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveDocument extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_REPLACED = 'replaced';

    public const STATUS_REVOKED = 'revoked';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'signed_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_document_id');
    }

    public function replacedDocument(): HasOne
    {
        return $this->hasOne(self::class, 'replaced_by_document_id');
    }

    public function path(): ?string
    {
        return $this->file_path ?: $this->local_path;
    }

    public function shortHash(): string
    {
        if (! $this->sha256_hash) {
            return '-';
        }

        return substr($this->sha256_hash, 0, 12).'...'.substr($this->sha256_hash, -8);
    }
}
