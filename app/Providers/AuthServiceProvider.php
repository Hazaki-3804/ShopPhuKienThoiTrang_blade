<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Định nghĩa Gate ở đây
        Gate::define('access-admin', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('manage-orders', function ($user) {
            return in_array($user->role_id, [1, 2]);
        });
    }
}
