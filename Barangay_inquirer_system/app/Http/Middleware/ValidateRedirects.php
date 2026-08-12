<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to validate redirect responses
 * Prevents open redirect vulnerabilities by validating all redirect URLs
 */
class ValidateRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the response
        $response = $next($request);

        // Check if this is a redirect response
        if ($response instanceof RedirectResponse) {
            $redirectUrl = $response->getTargetUrl();

            // Validate the redirect URL
            if (!$this->isValidRedirect($redirectUrl, $request)) {
                // Log the invalid redirect attempt for security
                $this->logInvalidRedirect($request, $redirectUrl);

                // Redirect to safe fallback (dashboard or home)
                return $this->getSafeRedirect($request);
            }
        }

        return $response;
    }

    /**
     * Check if a redirect URL is valid
     *
     * @param string $url The redirect URL
     * @param Request $request The current request
     * @return bool
     */
    protected function isValidRedirect(string $url, Request $request): bool
    {
        $validator = app('redirect.validator');

        return $validator->isValidRedirectUrl($url, $request);
    }

    /**
     * Get a safe redirect response based on user role
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function getSafeRedirect(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            // Redirect to role-based dashboard
            return match ($user->role) {
                'super_admin' => redirect()->route('superadmin.dashboard'),
                'admin' => redirect()->route('admin.dashboard'),
                default => redirect()->route('resident.dashboard'),
            };
        }

        // Not authenticated, redirect to home
        return redirect()->route('home');
    }

    /**
     * Log invalid redirect attempts for security monitoring
     *
     * @param Request $request
     * @param string $attemptedUrl
     * @return void
     */
    protected function logInvalidRedirect(Request $request, string $attemptedUrl): void
    {
        \Illuminate\Support\Facades\Log::warning('Invalid redirect attempt detected', [
            'attempted_url' => $attemptedUrl,
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->referrer(),
            'timestamp' => now(),
        ]);

        // Optionally create an activity log entry for admins
        try {
            if (class_exists(\App\Models\Activity::class) && $request->user()) {
                \App\Models\Activity::create([
                    'user_id' => $request->user()->id,
                    'action' => 'invalid_redirect_attempt',
                    'details' => [
                        'attempted_url' => $attemptedUrl,
                        'ip' => $request->ip(),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if activity log unavailable
        }
    }
}
