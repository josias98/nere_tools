<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', fn (User $user): bool => $user->is_active
            && $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_FINANCE]));

        RateLimiter::for('leave-document-upload', fn (Request $request) => [
            Limit::perMinute(8)->by($request->ip()),
        ]);
    }
}
