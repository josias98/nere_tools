<?php

use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tool:timesheets'])->group(function (): void {
    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/timesheets/csv', [TimesheetController::class, 'uploadCsv'])->middleware('throttle:10,1')->name('timesheets.csv');
    Route::get('/timesheets/csv/clear', [TimesheetController::class, 'clearCsv'])->name('timesheets.csv.clear');
    Route::post('/timesheets/generate', [TimesheetController::class, 'generate'])->middleware('throttle:5,1')->name('timesheets.generate');
    Route::get('/timesheets/results/{generation:uuid}', [TimesheetController::class, 'result'])->name('timesheets.result');
    Route::get('/timesheets/history', [TimesheetController::class, 'history'])->name('timesheets.history');
    Route::get('/timesheets/download/{file}', [TimesheetController::class, 'downloadFile'])->name('timesheets.download.file');
    Route::get('/timesheets/download-zip/{generation:uuid}', [TimesheetController::class, 'downloadZip'])->name('timesheets.download.zip');
});
