<?php

namespace App\Providers;

use App\Services\RedirectUrlValidator;
use Illuminate\Support\ServiceProvider;

class RedirectValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the RedirectUrlValidator as a singleton
        $this->app->singleton(RedirectUrlValidator::class, function ($app) {
            return new RedirectUrlValidator();
        });

        // Register with a shorter alias for helpers
        $this->app->singleton('redirect.validator', function ($app) {
            return $app->make(RedirectUrlValidator::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
