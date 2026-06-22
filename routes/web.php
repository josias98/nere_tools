<?php

use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Leaves\Admin\LeaveAdminController;
use App\Http\Controllers\Leaves\Admin\LeaveImportController;
use App\Http\Controllers\Leaves\Admin\LeaveValidatorController;
use App\Http\Controllers\Leaves\LeaveDashboardController;
use App\Http\Controllers\Leaves\LeaveDocumentController;
use App\Http\Controllers\Leaves\LeaveHistoryController;
use App\Http\Controllers\Leaves\LeaveRequestController;
use App\Http\Controllers\Leaves\LeaveValidationController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [MicrosoftAuthController::class, 'login'])->name('login');
    Route::get('/auth/microsoft/redirect', [MicrosoftAuthController::class, 'redirect'])
        ->name('auth.microsoft.redirect');
    Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])
        ->name('auth.microsoft.callback')
        ->withoutMiddleware('guest');
});

Route::post('/logout', [MicrosoftAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('tool:timesheets')->group(function (): void {
        Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
        Route::post('/timesheets/csv', [TimesheetController::class, 'uploadCsv'])->name('timesheets.csv');
        Route::get('/timesheets/csv/clear', [TimesheetController::class, 'clearCsv'])->name('timesheets.csv.clear');
        Route::post('/timesheets/generate', [TimesheetController::class, 'generate'])->name('timesheets.generate');
        Route::get('/timesheets/results/{generation:uuid}', [TimesheetController::class, 'result'])->name('timesheets.result');
        Route::get('/timesheets/history', [TimesheetController::class, 'history'])->name('timesheets.history');
        Route::get('/timesheets/download/{file}', [TimesheetController::class, 'downloadFile'])->name('timesheets.download.file');
        Route::get('/timesheets/download-zip/{generation:uuid}', [TimesheetController::class, 'downloadZip'])->name('timesheets.download.zip');
    });

    Route::view('/admin', 'admin.index')
        ->middleware('admin-area')
        ->name('admin.index');

    Route::middleware('tool:conges')->group(function (): void {
        Route::get('/conges', [LeaveDashboardController::class, 'index'])->name('leaves.index');
        Route::get('/conges/demande', [LeaveRequestController::class, 'create'])->name('leaves.create');
        Route::post('/conges/demande', [LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::get('/conges/historique', [LeaveHistoryController::class, 'index'])->name('leaves.history');
        Route::get('/conges/validations/en-attente', [LeaveValidationController::class, 'index'])->name('leaves.validations.index');
        Route::get('/conges/validations/{leaveRequest:uuid}', [LeaveValidationController::class, 'show'])->name('leaves.validations.show');
        Route::post('/conges/validations/{leaveRequest:uuid}/approuver', [LeaveValidationController::class, 'approve'])->name('leaves.validations.approve');
        Route::post('/conges/validations/{leaveRequest:uuid}/rejeter', [LeaveValidationController::class, 'reject'])->name('leaves.validations.reject');
        Route::get('/conges/documents/{document}/telecharger', LeaveDocumentController::class)->name('leaves.documents.download');
        Route::get('/conges/{leaveRequest:uuid}', [LeaveRequestController::class, 'show'])->name('leaves.show');
    });

    Route::middleware('admin-area')->group(function (): void {
        Route::get('/admin/conges', [LeaveAdminController::class, 'index'])->name('admin.leaves.index');
        Route::put('/admin/conges/collaborateurs/{employee}', [LeaveAdminController::class, 'updateEmployee'])->name('admin.leaves.employees.update');
        Route::put('/admin/conges/parametres', [LeaveAdminController::class, 'updateSettings'])->name('admin.leaves.settings.update');
        Route::post('/admin/conges/validateurs', [LeaveValidatorController::class, 'store'])->name('admin.leaves.validators.store');
        Route::put('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'update'])->name('admin.leaves.validators.update');
        Route::delete('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'destroy'])->name('admin.leaves.validators.destroy');
        Route::post('/admin/conges/import', LeaveImportController::class)->name('admin.leaves.import');
    });
});
