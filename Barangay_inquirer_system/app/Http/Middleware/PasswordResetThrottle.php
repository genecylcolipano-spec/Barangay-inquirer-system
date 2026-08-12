<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for rate limiting password reset requests
 * Limits: 3 requests per email per hour
 * Identifier: Email address (from request body)
 */
class PasswordResetThrottle
{
    protected const MAX_ATTEMPTS = 3;
    protected const DECAY_MINUTES = 60; // 1 hour

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $limiter = app(RateLimiter::class);

            // Get email from request
            $email = $this->getEmailFromRequest($request);

            if (!$email) {
                // Log missing email with full request context
                \Log::warning('Password reset request missing email', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request_data' => $request->all(),
                    'timestamp' => now()->toIso8601String(),
                ]);

                return response()->json(
                    ['message' => 'Request format is invalid.'],
                    422
                );
            }

            // Create a unique identifier for the email
            $identifier = 'password-reset:' . strtolower($email);

            // Check if email has exceeded rate limit
            if ($limiter->tooManyAttempts($identifier, self::MAX_ATTEMPTS, self::DECAY_MINUTES * 60)) {
                $retryAfter = $limiter->availableIn($identifier);
                
                // Log rate limit violation with full context
                \Log::warning('Password reset rate limit exceeded', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'retry_after' => $retryAfter,
                    'attempts' => $limiter->attempts($identifier),
                    'max_attempts' => self::MAX_ATTEMPTS,
                    'timestamp' => now()->toIso8601String(),
                    'request_id' => uniqid('req_'),
                ]);

                return $this->buildResponse($retryAfter, $email);
            }

            // Record this request attempt
            $limiter->hit($identifier, self::DECAY_MINUTES * 60);

            // Log the request (for monitoring)
            $attempts = $limiter->attempts($identifier);
            \Log::info('Password reset request', [
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempt' => $attempts,
                'remaining' => self::MAX_ATTEMPTS - $attempts,
                'max_attempts' => self::MAX_ATTEMPTS,
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('req_'),
            ]);

            $response = $next($request);

            // Add rate limit headers to response
            return $response
                ->header('X-RateLimit-Limit', self::MAX_ATTEMPTS)
                ->header('X-RateLimit-Remaining', max(0, self::MAX_ATTEMPTS - $limiter->attempts($identifier)))
                ->header('X-RateLimit-Reset', now()->addMinutes(self::DECAY_MINUTES)->timestamp);

        } catch (\Exception $e) {
            // Log all exceptions with full context
            \Log::error('Password reset middleware error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('req_'),
            ]);

            // Return generic error to user
            return response()->json(
                ['message' => 'An error occurred. Please try again later.'],
                500
            );
        }
    }

    /**
     * Extract email from request
     * Supports multiple password reset endpoints
     */
    private function getEmailFromRequest(Request $request): ?string
    {
        // Password email (forgot password form)
        if ($request->has('email')) {
            $email = $request->input('email');
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        // From POST data in password/email endpoint
        if ($request->isMethod('post')) {
            $email = $request->post('email');
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Build rate limit exceeded response
     */
    private function buildResponse(int $retryAfter, string $email): Response
    {
        $minutes = ceil($retryAfter / 60);

        return response()->json(
            [
                'message' => "Too many password reset requests. Please try again in {$minutes} minute(s).",
                'email' => $email,
                'retry_after' => $retryAfter,
            ],
            429
        )
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', self::MAX_ATTEMPTS)
            ->header('X-RateLimit-Remaining', 0)
            ->header('X-RateLimit-Reset', now()->addMinutes(self::DECAY_MINUTES)->timestamp);
    }
}
