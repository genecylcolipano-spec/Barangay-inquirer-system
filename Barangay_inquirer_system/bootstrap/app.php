<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(\App\Http\Middleware\SetLocale::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'clerk.auth' => \App\Http\Middleware\ClerkAuth::class,
            'rls' => \App\Http\Middleware\EnforceRowLevelSecurity::class,
            'check_login_attempts' => \App\Http\Middleware\CheckLoginAttempts::class,
            'throttle_auth' => \App\Http\Middleware\RoleBasedThrottle::class . ':auth',
            'throttle_user_data' => \App\Http\Middleware\RoleBasedThrottle::class . ':user_data',
            'throttle_media' => \App\Http\Middleware\RoleBasedThrottle::class . ':media',
            'throttle_public' => \App\Http\Middleware\RoleBasedThrottle::class . ':public_data',
            'throttle_contact' => \App\Http\Middleware\RoleBasedThrottle::class . ':contact',
            'throttle_password_reset' => \App\Http\Middleware\PasswordResetThrottle::class,
            'validate_redirects' => \App\Http\Middleware\ValidateRedirects::class,
            'enforce_file_policy' => \App\Http\Middleware\EnforceFileStoragePolicy::class,
            'jwt' => \App\Http\Middleware\JwtTokenMiddleware::class,
        ]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\EnforceRowLevelSecurity::class);
        $middleware->append(\App\Http\Middleware\ValidateRedirects::class);
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class
        );
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        // Exclude password reset route from CSRF verification
        $middleware->validateCsrfTokens(except: [
            '/password/email',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
