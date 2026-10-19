<?php

use App\Http\Controllers\PdfCraft\PdfCraftController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tool:pdfcraft'])->group(function (): void {
    Route::get('/pdfcraft/api/session', [PdfCraftController::class, 'session'])->name('pdfcraft.session');
    Route::post('/pdfcraft/api/files', [PdfCraftController::class, 'store'])->middleware('throttle:30,1')->name('pdfcraft.files.store');
    Route::get('/pdfcraft/files/{pdfCraftFile}', [PdfCraftController::class, 'download'])->name('pdfcraft.files.download');
    Route::get('/pdfcraft/history', [PdfCraftController::class, 'history'])->name('pdfcraft.history');
    Route::get('/pdfcraft/{path?}', [PdfCraftController::class, 'page'])
        ->where('path', '.*')
        ->name('pdfcraft.index');
});
