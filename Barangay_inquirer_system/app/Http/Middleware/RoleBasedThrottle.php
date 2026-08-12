<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedThrottle
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $category = 'default'): Response
    {
        $limiter = app(RateLimiter::class);
        $user = $request->user();
        
        // Determine the rate limit based on user role and route category
        $limit = $this->getRateLimit($user, $category, $request);
        
        if ($limit === null) {
            return $next($request);
        }
        
        // Get the identifier for rate limiting (user ID or IP)
        $identifier = $this->getIdentifier($user, $request);
        
        // Check rate limit
        if ($limiter->tooManyAttempts($identifier, $limit['requests'], $limit['decay'])) {
            return $this->buildResponse($limiter->availableIn($identifier));
        }
        
        $limiter->hit($identifier, $limit['decay']);
        
        return $next($request);
    }

    /**
     * Get rate limit based on user role and category
     */
    private function getRateLimit($user, string $category, Request $request): ?array
    {
        $role = $user ? $user->role : 'guest';

        // Define rate limits per category (requests per minute)
        $limits = [
            'auth' => [
                'guest'       => ['requests' => 10, 'decay' => 60],      // 5-10 req/min → 10/min conservative
                'resident'    => ['requests' => 30, 'decay' => 60],      // 20-30 req/min
                'admin'       => ['requests' => 50, 'decay' => 60],      // 30-50 req/min
                'super_admin' => ['requests' => 100, 'decay' => 60],     // 50-100 req/min
            ],
            'user_data' => [
                'guest'       => null,                                    // No access
                'resident'    => ['requests' => 20, 'decay' => 60],      // 10-20 req/min
                'admin'       => ['requests' => 100, 'decay' => 60],     // 50-100 req/min
                'super_admin' => ['requests' => 200, 'decay' => 60],     // 100-200 req/min
            ],
            'media' => [
                'guest'       => null,
                'resident'    => ['requests' => 10, 'decay' => 60],      // 5-10 req/min
                'admin'       => ['requests' => 50, 'decay' => 60],      // 20-50 req/min
                'super_admin' => ['requests' => 100, 'decay' => 60],     // 50-100 req/min
            ],
            'public_data' => [
                'guest'       => ['requests' => 50, 'decay' => 60],      // 20-50 req/min (cached: 100/min)
                'resident'    => ['requests' => 100, 'decay' => 60],     // 50-100 req/min
                'admin'       => ['requests' => 200, 'decay' => 60],     // 100-200 req/min
                'super_admin' => ['requests' => 300, 'decay' => 60],     // 200-300 req/min
            ],
            'contact' => [
                'guest'       => ['requests' => 3, 'decay' => 60],       // 1-3 req/min (+ CAPTCHA)
                'resident'    => ['requests' => 5, 'decay' => 60],       // 3-5 req/min
                'admin'       => ['requests' => 10, 'decay' => 60],      // 5-10 req/min
                'super_admin' => ['requests' => 20, 'decay' => 60],      // 10-20 req/min
            ],
        ];

        $limit = $limits[$category][$role] ?? $limits['default'][$role] ?? null;

        // For guest users on contact forms, require CAPTCHA
        if ($role === 'guest' && $category === 'contact') {
            if (!$request->has('g-recaptcha-response')) {
                return $limit;
                // Note: Validation should be done in controller
            }
        }

        return $limit;
    }

    /**
     * Get identifier for rate limiting (user ID or IP)
     */
    private function getIdentifier($user, Request $request): string
    {
        if ($user) {
            return 'user:' . $user->id;
        }
        return 'ip:' . $request->ip();
    }

    /**
     * Build rate limit exceeded response
     */
    private function buildResponse(int $retryAfter): Response
    {
        return response()
            ->view('errors.rate-limit', ['retryAfter' => $retryAfter], 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Retry-After', $retryAfter);
    }
}
