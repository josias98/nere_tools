<?php

namespace App\Services\PdfCraft;

use App\Models\PdfCraftFile;
use Illuminate\Support\Facades\Storage;

class PdfCraftPruner
{
    public function prune(int $limit = 50): int
    {
        $deleted = 0;

        foreach (PdfCraftFile::query()->where('expires_at', '<=', now())->orderBy('id')->limit($limit)->get() as $file) {
            if (Storage::disk('local')->exists($file->file_path)
                && ! Storage::disk('local')->delete($file->file_path)) {
                continue;
            }

            $file->delete();
            $deleted++;
        }

        return $deleted;
    }
}
