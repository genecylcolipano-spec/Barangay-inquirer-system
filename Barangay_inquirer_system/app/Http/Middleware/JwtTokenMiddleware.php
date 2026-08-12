<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\JwtService;
use Illuminate\Support\Facades\Log;

/**
 * JWT Token Validation Middleware
 * 
 * Validates JWT access tokens in Authorization header
 * Extracts token claims and sets authenticated user
 */
class JwtTokenMiddleware
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from Authorization header
        $token = $this->extractToken($request);

        if (!$token) {
            Log::warning('Missing JWT token', [
                'path' => $request->getPathInfo(),
                'ip' => $request->ip(),
                'context' => 'missing_auth_header',
            ]);

            return response()->json([
                'message' => 'Authorization token missing.',
                'error' => 'unauthorized'
            ], 401);
        }

        // Verify token
        $payload = $this->jwtService->verifyAccessToken($token);

        if (!$payload) {
            Log::warning('Invalid JWT token', [
                'path' => $request->getPathInfo(),
                'ip' => $request->ip(),
                'context' => 'invalid_jwt_token',
            ]);

            return response()->json([
                'message' => 'Authorization token invalid or expired.',
                'error' => 'unauthorized'
            ], 401);
        }

        // Get user from token
        $user = $this->jwtService->getUserFromToken($payload);

        if (!$user) {
            Log::warning('Token user not found', [
                'user_id' => $payload['user_id'] ?? null,
                'ip' => $request->ip(),
                'context' => 'token_user_not_found',
            ]);

            return response()->json([
                'message' => 'User not found.',
                'error' => 'unauthorized'
            ], 401);
        }

        // Authenticate the user for this request
        auth()->setUser($user);

        // Store token payload in request for later use
        $request->attributes->set('jwt_payload', $payload);

        Log::debug('JWT token validated', [
            'user_id' => $user->id,
            'jti' => $payload['jti'] ?? null,
            'context' => 'jwt_authenticated',
        ]);

        return $next($request);
    }

    /**
     * Extract JWT from Authorization header
     */
    protected function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return null;
        }

        // Bearer token format: "Bearer <token>"
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
