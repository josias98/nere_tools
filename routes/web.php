<?php

use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\DashboardController;
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

    Route::view('/timesheets', 'tools.timesheets')
        ->middleware('role:'.implode(',', [
            User::ROLE_ADMIN,
            User::ROLE_FINANCE,
            User::ROLE_DIRECTION,
        ]))
        ->name('timesheets.index');

    Route::view('/admin', 'admin.index')
        ->middleware('role:'.User::ROLE_ADMIN)
        ->name('admin.index');
});
