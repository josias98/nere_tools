<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'user_id', 'operation_id', 'tool_slug', 'kind', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'file_path', 'expires_at'])]
class PdfCraftFile extends Model
{
    protected $table = 'pdfcraft_files';

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}
