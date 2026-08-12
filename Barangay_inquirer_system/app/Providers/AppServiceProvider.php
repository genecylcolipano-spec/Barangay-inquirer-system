<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
        // Configure rate limiting
        RateLimiter::for('api', function () {
            return Limit::perMinute(60);
        });

        RateLimiter::for('login', function () {
            return Limit::perMinute(5);
        });

        // Share notifications data with all superadmin views
        View::composer('superadmin.*', function ($view) {
            if (auth()->check() && auth()->user()->role === 'super_admin') {
                // Only set if not already set by controller (controller takes precedence)
                if (!$view->offsetExists('notifications')) {
                    $view->with('notifications', auth()->user()->unreadNotifications()->limit(5)->get());
                }
                $view->with('unreadCount', auth()->user()->unreadNotifications()->count());
            }
        });

        // Share notifications data with all admin views
        View::composer('admin.*', function ($view) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                // Only set if not already set by controller (controller takes precedence)
                if (!$view->offsetExists('notifications')) {
                    $view->with('notifications', auth()->user()->unreadNotifications()->limit(5)->get());
                }
                $view->with('unreadCount', auth()->user()->unreadNotifications()->count());
            }
        });
    }
}
