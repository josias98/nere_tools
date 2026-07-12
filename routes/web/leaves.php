<?php

use App\Http\Controllers\Leaves\LeaveAttachmentController;
use App\Http\Controllers\Leaves\LeaveDashboardController;
use App\Http\Controllers\Leaves\LeaveDocumentController;
use App\Http\Controllers\Leaves\LeaveDocumentVerificationController;
use App\Http\Controllers\Leaves\LeaveHistoryController;
use App\Http\Controllers\Leaves\LeaveRequestController;
use App\Http\Controllers\Leaves\LeaveValidationController;
use Illuminate\Support\Facades\Route;

Route::get('/conges/verify', [LeaveDocumentVerificationController::class, 'index'])->name('leaves.verify.index');
Route::get('/conges/verify/{token}', [LeaveDocumentVerificationController::class, 'show'])->name('leaves.verify.show');
Route::post('/conges/verify/upload', [LeaveDocumentVerificationController::class, 'upload'])
    ->middleware('throttle:leave-document-upload')
    ->name('leaves.verify.upload');

Route::middleware(['auth', 'tool:conges'])->group(function (): void {
    Route::get('/conges', [LeaveDashboardController::class, 'index'])->name('leaves.index');
    Route::get('/conges/demande', [LeaveRequestController::class, 'create'])->name('leaves.create');
    Route::post('/conges/demande', [LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::get('/conges/historique', [LeaveHistoryController::class, 'index'])->name('leaves.history');
    Route::get('/conges/validations/en-attente', [LeaveValidationController::class, 'index'])->name('leaves.validations.index');
    Route::get('/conges/validations/{leaveRequest:uuid}', [LeaveValidationController::class, 'show'])->name('leaves.validations.show');
    Route::post('/conges/validations/{leaveRequest:uuid}/approuver', [LeaveValidationController::class, 'approve'])->name('leaves.validations.approve');
    Route::post('/conges/validations/{leaveRequest:uuid}/rejeter', [LeaveValidationController::class, 'reject'])->name('leaves.validations.reject');
    Route::get('/conges/documents/{document}/telecharger', LeaveDocumentController::class)->name('leaves.documents.download');
    Route::get('/conges/justificatifs/{attachment}/telecharger', LeaveAttachmentController::class)->name('leaves.attachments.download');
    Route::get('/conges/{leaveRequest:uuid}', [LeaveRequestController::class, 'show'])->name('leaves.show');
});
