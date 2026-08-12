<?php

/**
 * Helper functions for safe redirect operations
 */

use App\Services\RedirectUrlValidator;
use Illuminate\Http\RedirectResponse;

/**
 * Safely redirect to a URL or route after validating it
 * Falls back to a default URL if validation fails
 *
 * @param string $to The URL/route to redirect to
 * @param int $status HTTP status code
 * @param array $headers Headers for the response
 * @param string|null $fallback The fallback URL if validation fails
 * @return RedirectResponse
 */
function safe_redirect(
    string $to,
    int $status = 302,
    array $headers = [],
    ?string $fallback = null
): RedirectResponse {
    $validator = app(RedirectUrlValidator::class);

    // Determine fallback
    if ($fallback === null) {
        $fallback = route('home');
    }

    // Validate and get safe URL
    $safeUrl = $validator->getSafeRedirectUrl($to, $fallback);

    return redirect($safeUrl, $status, $headers);
}

/**
 * Safely redirect to a route after validating it
 *
 * @param string $name The route name
 * @param mixed $parameters Route parameters
 * @param int $status HTTP status code
 * @param array $headers Headers
 * @return RedirectResponse
 */
function safe_redirect_route(
    string $name,
    mixed $parameters = [],
    int $status = 302,
    array $headers = []
): RedirectResponse {
    $validator = app(RedirectUrlValidator::class);

    // Check if route is in allowlist
    if (!in_array($name, $validator->getAllowedRoutes())) {
        \Illuminate\Support\Facades\Log::warning("Redirect to unauthorized route attempted: {$name}");

        return redirect()->route('home', status: $status, headers: $headers);
    }

    return redirect()->route($name, $parameters, $status, $headers);
}

/**
 * Check if a URL is safe to redirect to
 *
 * @param string $url
 * @return bool
 */
function is_safe_redirect_url(string $url): bool
{
    return app(RedirectUrlValidator::class)->isValidRedirectUrl($url);
}

/**
 * Get the safe redirect URL or fallback
 *
 * @param string $url
 * @param string $fallback
 * @return string
 */
function get_safe_redirect_url(string $url, string $fallback = '/'): string
{
    return app(RedirectUrlValidator::class)->getSafeRedirectUrl($url, $fallback);
}
