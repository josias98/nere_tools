<?php

namespace App\Modules\Timesheets\Support;

use App\Models\TimesheetGeneration;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class TimesheetArchiveBuilder
{
    public function build(TimesheetGeneration $generation): string
    {
        $zipPath = "timesheets/zips/{$generation->uuid}.zip";
        Storage::disk('local')->makeDirectory('timesheets/zips');

        $zip = new ZipArchive;
        $absolutePath = Storage::disk('local')->path($zipPath);

        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de creer le fichier ZIP.');
        }

        foreach ($generation->files as $file) {
            $zip->addFile(Storage::disk('local')->path($file->file_path), $file->file_name);
        }

        $zip->close();

        return $zipPath;
    }
}
