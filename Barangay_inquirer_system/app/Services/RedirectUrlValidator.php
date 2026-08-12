<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Service to validate redirect URLs against an allowlist
 * Prevents open redirect vulnerabilities and phishing attacks
 */
class RedirectUrlValidator
{
    /**
     * List of allowed redirect routes (by route name)
     */
    protected array $allowedRoutes = [
        // Admin routes
        'admin.dashboard',
        'admin.users.index',
        'admin.users.show',
        'admin.requests.index',
        'admin.requests.show',
        'admin.announcements.index',
        'admin.announcements.show',
        'admin.announcements.create',
        'admin.announcements.edit',
        'admin.settings.index',
        'admin.settings.photo',
        'admin.profile',
        'admin.notifications.index',

        // Resident routes
        'resident.dashboard',
        'resident.requests',
        'resident.requests.create',
        'resident.requests.show',
        'resident.requests.index',
        'resident.profile',
        'resident.settings',
        'resident.notifications',

        // Super Admin routes
        'superadmin.dashboard',
        'superadmin.users.index',
        'superadmin.users.show',
        'superadmin.requests.index',
        'superadmin.requests.show',
        'superadmin.announcements.index',
        'superadmin.announcements.show',
        'superadmin.announcements.create',
        'superadmin.announcements.edit',
        'superadmin.admins.index',
        'superadmin.admins.show',
        'superadmin.admins.create',
        'superadmin.admins.edit',
        'superadmin.activity-logs',
        'superadmin.settings',
        'superadmin.profile',
        'superadmin.system-health',
        'superadmin.notifications.index',

        // Public routes
        'home',
        'login',
        'register',
        'password.request',
        'password.reset',
        'verification.notice',
    ];

    /**
     * Regex patterns for allowed URLs (for complex paths)
     * Items can be regex patterns for flexible matching
     */
    protected array $allowedPatterns = [];

    public function __construct()
    {
        // Initialize patterns with app domain
        $appDomain = config('app.domain', 'localhost');
        $this->allowedPatterns = [
            '#^https?://[a-z0-9-]+\.' . preg_quote($appDomain, '#') . '(/|$)#i',
        ];
    }

    /**
     * Validate if a URL/route is allowed for redirection
     *
     * @param string $url The URL or route name to validate
     * @param Request|null $request The current request (optional)
     * @return bool True if URL is safe to redirect to
     */
    public function isValidRedirectUrl(string $url, ?Request $request = null): bool
    {
        if (empty($url)) {
            return false;
        }

        // Trim whitespace
        $url = trim($url);

        // Reject if starts with protocol-relative URLs (//evil.com)
        if (str_starts_with($url, '//')) {
            return false;
        }

        // Reject JavaScript: and data: protocols and other dangerous schemes
        if ($this->containsDangerousProtocol($url)) {
            return false;
        }

        // If it's a relative URL (starts with /), validate it
        if (str_starts_with($url, '/')) {
            return $this->isValidRelativeUrl($url, $request);
        }

        // If it's an absolute URL, only allow if it's internal
        if (str_contains($url, '://')) {
            return $this->isInternalUrl($url);
        }

        return false;
    }

    /**
     * Validate a relative URL path
     *
     * @param string $path The relative path (starts with /)
     * @param Request|null $request The current request
     * @return bool
     */
    protected function isValidRelativeUrl(string $path, ?Request $request = null): bool
    {
        // Remove query string and fragment for route matching
        $pathWithoutQuery = explode('?', explode('#', $path)[0])[0];

        // Check if path matches any allowed route
        if ($this->pathMatchesAllowedRoutes($pathWithoutQuery)) {
            return true;
        }

        // Check allowlist patterns
        foreach ($this->allowedPatterns as $pattern) {
            if (@preg_match($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if path matches any of the allowed routes
     *
     * @param string $path The path to check
     * @return bool
     */
    protected function pathMatchesAllowedRoutes(string $path): bool
    {
        // Fallback: check common internal paths
        $pathPrefix = explode('/', ltrim($path, '/'))[0];
        $allowedPrefixes = ['admin', 'resident', 'superadmin', 'login', 'register', 'password', 'api', 'contact', 'verify'];

        return in_array($pathPrefix, $allowedPrefixes);
    }

    /**
     * Check if a full URL is internal (same domain as app)
     *
     * @param string $url The absolute URL to check
     * @return bool
     */
    protected function isInternalUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host'])) {
            return false;
        }

        // Get the app's host
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? config('app.domain');

        // Allow only same domain
        return $parsedUrl['host'] === $appHost ||
               $parsedUrl['host'] === 'localhost' ||
               str_starts_with($parsedUrl['host'], 'localhost:');
    }

    /**
     * Check if URL contains dangerous protocols
     *
     * @param string $url
     * @return bool
     */
    protected function containsDangerousProtocol(string $url): bool
    {
        $dangerousProtocols = [
            'javascript:',
            'data:',
            'vbscript:',
            'file:',
            'about:',
        ];

        $urlLower = strtolower($url);

        foreach ($dangerousProtocols as $protocol) {
            if (str_starts_with($urlLower, $protocol)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a safe redirect URL, returning a fallback if the URL is invalid
     *
     * @param string $url The intended redirect URL
     * @param string $fallback The fallback URL if validation fails
     * @param Request|null $request
     * @return string The validated URL or fallback
     */
    public function getSafeRedirectUrl(
        string $url,
        string $fallback = '/',
        ?Request $request = null
    ): string {
        if ($this->isValidRedirectUrl($url, $request)) {
            return $url;
        }

        return $fallback;
    }

    /**
     * Add a route to the allowlist
     *
     * @param string|array $routes Route name(s) to add
     * @return void
     */
    public function addAllowedRoutes($routes): void
    {
        if (is_string($routes)) {
            $routes = [$routes];
        }

        $this->allowedRoutes = array_unique(array_merge($this->allowedRoutes, $routes));
    }

    /**
     * Add a pattern to the allowlist
     *
     * @param string $pattern Regex pattern to add
     * @return void
     */
    public function addAllowedPattern(string $pattern): void
    {
        $this->allowedPatterns[] = $pattern;
    }

    /**
     * Get the list of allowed routes
     *
     * @return array
     */
    public function getAllowedRoutes(): array
    {
        return $this->allowedRoutes;
    }

    /**
     * Get the list of allowed patterns
     *
     * @return array
     */
    public function getAllowedPatterns(): array
    {
        return $this->allowedPatterns;
    }
}
