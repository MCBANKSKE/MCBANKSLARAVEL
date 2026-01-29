<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

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
        $this->configureEmailVerification();
    }

    /**
     * Configure email verification middleware.
     */
    protected function configureEmailVerification(): void
    {
        // Ensure the email verification middleware is applied to all routes
        $this->app['router']->aliasMiddleware('verified', EnsureEmailIsVerified::class);
    }
}
