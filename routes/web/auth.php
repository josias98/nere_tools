<?php

use App\Http\Controllers\Auth\MicrosoftAuthController;
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
