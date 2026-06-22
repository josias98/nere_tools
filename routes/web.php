<?php

use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TimesheetController;
use App\Models\User;
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

    Route::middleware('role:'.implode(',', [
        User::ROLE_ADMIN,
        User::ROLE_FINANCE,
        User::ROLE_DIRECTION,
    ]))->group(function (): void {
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
        ->middleware('role:'.User::ROLE_ADMIN)
        ->name('admin.index');

    Route::get('/conges', [\App\Http\Controllers\Leaves\LeaveDashboardController::class, 'index'])->name('leaves.index');
    Route::get('/conges/demande', [\App\Http\Controllers\Leaves\LeaveRequestController::class, 'create'])->name('leaves.create');
    Route::post('/conges/demande', [\App\Http\Controllers\Leaves\LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::get('/conges/historique', [\App\Http\Controllers\Leaves\LeaveHistoryController::class, 'index'])->name('leaves.history');
    Route::get('/conges/{leaveRequest:uuid}', [\App\Http\Controllers\Leaves\LeaveRequestController::class, 'show'])->name('leaves.show');
});
