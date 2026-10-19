<?php

use App\Http\Controllers\Signatures\SignatureDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tool:signatures'])->prefix('signatures')->name('signatures.')->group(function (): void {
    Route::get('/', [SignatureDocumentController::class, 'index'])->name('index');
    Route::post('/', [SignatureDocumentController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('/{document:uuid}', [SignatureDocumentController::class, 'show'])->name('show');
    Route::post('/{document:uuid}/signer', [SignatureDocumentController::class, 'sign'])->middleware('throttle:6,1')->name('sign');
    Route::get('/{document:uuid}/fichier/{version}', [SignatureDocumentController::class, 'file'])->name('file');
    Route::get('/{document:uuid}/preuve', [SignatureDocumentController::class, 'evidence'])->name('evidence');
});
