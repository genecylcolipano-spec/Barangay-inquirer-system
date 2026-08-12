<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * JWT Authentication Controller
 * 
 * Handles token issuance, refresh, and revocation
 * Supports both Clerk authentication and traditional login
 */
class AuthController extends Controller
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Issue tokens for authenticated user
     * 
     * Can be used with Clerk authenticated requests or traditional login
     * POST /api/auth/token
     */
    public function issueToken(Request $request)
    {
        try {
            // Get authenticated user
            $user = auth()->user();

            if (!$user) {
                Log::warning('Token request with no authenticated user', [
                    'ip' => $request->ip(),
                    'context' => 'no_authenticated_user',
                ]);

                return response()->json([
                    'message' => 'User not authenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            // Issue tokens
            $tokens = $this->jwtService->issueTokens($user, $request);

            return response()->json($tokens, 200);

        } catch (\Exception $e) {
            Log::error('Token issuance error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'context' => 'token_issuance_error',
            ]);

            return response()->json([
                'message' => 'Failed to issue tokens. Please try again.',
                'error' => 'server_error'
            ], 500);
        }
    }

    /**
     * Refresh access token
     * 
     * Implements refresh token rotation
     * POST /api/auth/refresh
     * Body: {"refresh_token": "..."}
     */
    public function refresh(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            $refreshToken = $request->input('refresh_token');

            // Refresh the access token
            $tokens = $this->jwtService->refreshAccessToken($refreshToken, $request);

            if (!$tokens) {
                Log::warning('Token refresh failed - invalid token', [
                    'ip' => $request->ip(),
                    'context' => 'refresh_failed_invalid_token',
                ]);

                return response()->json([
                    'message' => 'Refresh token invalid or expired.',
                    'error' => 'invalid_refresh_token'
                ], 401);
            }

            return response()->json($tokens, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Token refresh validation failed', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
                'context' => 'refresh_validation_failed',
            ]);

            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
                'error' => 'validation_error'
            ], 422);

        } catch (\Exception $e) {
            Log::error('Token refresh error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'context' => 'token_refresh_error',
            ]);

            return response()->json([
                'message' => 'Failed to refresh token. Please try again.',
                'error' => 'server_error'
            ], 500);
        }
    }

    /**
     * Revoke refresh token (logout from one device)
     * 
     * POST /api/auth/logout
     * Body: {"refresh_token": "..."}
     * Requires: JWT authentication
     */
    public function logout(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            $refreshToken = $request->input('refresh_token');

            if ($this->jwtService->revokeRefreshToken($refreshToken, $user)) {
                return response()->json([
                    'message' => 'Logged out successfully.'
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to revoke token.',
                'error' => 'revocation_failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Logout error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'context' => 'logout_error',
            ]);

            return response()->json([
                'message' => 'An error occurred. Please try again.',
                'error' => 'server_error'
            ], 500);
        }
    }

    /**
     * Revoke all refresh tokens (logout from all devices)
     * 
     * POST /api/auth/logout-all
     * Requires: JWT authentication
     */
    public function logoutAll(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            if ($this->jwtService->revokeAllRefreshTokens($user)) {
                return response()->json([
                    'message' => 'Logged out from all devices.'
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to revoke all tokens.',
                'error' => 'revocation_failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Logout all error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'context' => 'logout_all_error',
            ]);

            return response()->json([
                'message' => 'An error occurred. Please try again.',
                'error' => 'server_error'
            ], 500);
        }
    }

    /**
     * Get active sessions (refresh tokens)
     * 
     * GET /api/auth/sessions
     * Requires: JWT authentication
     */
    public function getSessions(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            $sessions = JwtService::getActiveTokensForUser($user);

            return response()->json([
                'sessions' => $sessions->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'ip_address' => $token->ip_address,
                        'user_agent' => $token->user_agent,
                        'expires_at' => $token->expires_at,
                        'created_at' => $token->created_at,
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get sessions error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'context' => 'get_sessions_error',
            ]);

            return response()->json([
                'message' => 'Failed to retrieve sessions.',
                'error' => 'server_error'
            ], 500);
        }
    }

    /**
     * Revoke specific session by ID
     * 
     * DELETE /api/auth/sessions/{sessionId}
     * Requires: JWT authentication
     */
    public function revokeSession(Request $request, $sessionId)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            $token = \App\Models\RefreshToken::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$token) {
                return response()->json([
                    'message' => 'Session not found.',
                    'error' => 'not_found'
                ], 404);
            }

            $token->revoke();

            return response()->json([
                'message' => 'Session revoked.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Revoke session error', [
                'user_id' => auth()->id(),
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'context' => 'revoke_session_error',
            ]);

            return response()->json([
                'message' => 'Failed to revoke session.',
                'error' => 'server_error'
            ], 500);
        }
    }
}
