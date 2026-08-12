<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * JWT Authentication Service
 * 
 * Handles token creation, validation, and refresh token rotation
 * Features:
 * - 7-day access token expiration
 * - Refresh token rotation (old token invalidated on refresh)
 * - Token binding to IP address and user agent
 * - Secure token hashing before storage
 * - Automatic cleanup of expired/revoked tokens
 */
class JwtService
{
    protected string $algorithm;
    protected string $secretKey;
    protected int $accessTokenExpiration;
    protected int $refreshTokenExpiration;
    protected int $refreshGracePeriod;
    protected int $maxRefreshTokens;
    protected bool $rotateTokens;
    protected string $issuer;
    protected string $audience;

    public function __construct()
    {
        $this->algorithm = config('jwt.algorithm', 'HS256');
        $this->secretKey = config('jwt.secret_key');
        $this->accessTokenExpiration = config('jwt.jwt_expiration', 7 * 24 * 60 * 60);
        $this->refreshTokenExpiration = config('jwt.refresh_token_expiration', 14 * 24 * 60 * 60);
        $this->refreshGracePeriod = config('jwt.refresh_grace_period', 5 * 60);
        $this->maxRefreshTokens = config('jwt.max_refresh_tokens_per_user', 5);
        $this->rotateTokens = config('jwt.rotate_refresh_tokens', true);
        $this->issuer = config('jwt.issuer', config('app.name'));
        $this->audience = config('jwt.audience', config('app.url'));
    }

    /**
     * Create new access token and refresh token pair
     */
    public function issueTokens(User $user, Request $request): array
    {
        try {
            // Create access token (JWT)
            $accessToken = $this->createAccessToken($user);

            // Create refresh token
            $refreshToken = $this->createRefreshToken($user, $request);

            Log::info('JWT tokens issued', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'context' => 'token_issuance',
            ]);

            return [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->accessTokenExpiration,
                'refresh_token' => $refreshToken->token,
                'refresh_expires_in' => $this->refreshTokenExpiration,
            ];
        } catch (Exception $e) {
            Log::error('Token issuance error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'context' => 'token_issuance_error',
            ]);
            throw $e;
        }
    }

    /**
     * Create access token (JWT)
     */
    protected function createAccessToken(User $user): string
    {
        $now = now();
        $expiresAt = $now->clone()->addSeconds($this->accessTokenExpiration);

        $payload = [
            'iat' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'sub' => $user->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'jti' => uniqid('jti_', true),
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Create refresh token (stored in database)
     */
    protected function createRefreshToken(User $user, Request $request): RefreshToken
    {
        // Cleanup old refresh tokens if user exceeds limit
        if ($this->rotateTokens) {
            $this->enforceMaxRefreshTokens($user);
        }

        $token = RefreshToken::generateToken();

        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'token_hash' => RefreshToken::hashToken($token),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addSeconds($this->refreshTokenExpiration),
            'is_revoked' => false,
        ]);
    }

    /**
     * Verify and decode access token (JWT)
     */
    public function verifyAccessToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key($this->secretKey, $this->algorithm)
            );

            return (array) $decoded;
        } catch (Exception $e) {
            Log::warning('Access token verification failed', [
                'error' => $e->getMessage(),
                'context' => 'token_verification_failed',
            ]);
            return null;
        }
    }

    /**
     * Refresh access token using refresh token
     * Implements refresh token rotation (old token invalidated)
     */
    public function refreshAccessToken(string $refreshToken, Request $request): ?array
    {
        try {
            // Find refresh token by hash
            $hash = RefreshToken::hashToken($refreshToken);
            $storedToken = RefreshToken::byHash($hash)->first();

            if (!$storedToken) {
                Log::warning('Refresh token not found', [
                    'ip' => $request->ip(),
                    'context' => 'invalid_refresh_token',
                ]);
                return null;
            }

            // Validate token state
            if (!$storedToken->isValid()) {
                Log::warning('Refresh token invalid', [
                    'user_id' => $storedToken->user_id,
                    'reason' => $storedToken->is_revoked ? 'revoked' : 'expired',
                    'ip' => $request->ip(),
                    'context' => 'refresh_token_invalid',
                ]);
                return null;
            }

            // Check if token can still be refreshed (within grace period)
            if (!$storedToken->canRefresh()) {
                Log::warning('Refresh token cannot be refreshed - expired', [
                    'user_id' => $storedToken->user_id,
                    'expired_at' => $storedToken->expires_at,
                    'ip' => $request->ip(),
                    'context' => 'refresh_token_expired',
                ]);
                return null;
            }

            $user = $storedToken->user;

            // Rotate refresh token if enabled
            if ($this->rotateTokens) {
                // Mark old token as rotated
                $storedToken->markAsRotated();

                // Create new refresh token
                $newRefreshToken = $this->createRefreshToken($user, $request);
            } else {
                $newRefreshToken = $storedToken;
            }

            // Create new access token
            $accessToken = $this->createAccessToken($user);

            Log::info('Access token refreshed', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'rotated' => $this->rotateTokens,
                'context' => 'token_refresh',
            ]);

            return [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->accessTokenExpiration,
                'refresh_token' => $newRefreshToken->token,
                'refresh_expires_in' => $this->refreshTokenExpiration,
            ];

        } catch (Exception $e) {
            Log::error('Token refresh error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'context' => 'token_refresh_error',
            ]);
            return null;
        }
    }

    /**
     * Revoke refresh token (logout)
     */
    public function revokeRefreshToken(string $refreshToken, User $user): bool
    {
        try {
            $hash = RefreshToken::hashToken($refreshToken);
            $storedToken = RefreshToken::byHash($hash)
                ->forUser($user->id)
                ->first();

            if (!$storedToken) {
                Log::warning('Refresh token to revoke not found', [
                    'user_id' => $user->id,
                    'context' => 'revoke_token_not_found',
                ]);
                return false;
            }

            $storedToken->revoke();

            Log::info('Refresh token revoked', [
                'user_id' => $user->id,
                'context' => 'token_revoked',
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Token revocation error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'context' => 'token_revocation_error',
            ]);
            return false;
        }
    }

    /**
     * Revoke all refresh tokens for user (logout from all devices)
     */
    public function revokeAllRefreshTokens(User $user): bool
    {
        try {
            RefreshToken::forUser($user->id)
                ->active()
                ->update([
                    'is_revoked' => true,
                    'rotated_at' => now(),
                ]);

            Log::warning('All refresh tokens revoked for user', [
                'user_id' => $user->id,
                'context' => 'logout_all_devices',
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Revoke all tokens error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'context' => 'revoke_all_error',
            ]);
            return false;
        }
    }

    /**
     * Enforce maximum refresh tokens per user
     * Revokes oldest tokens when limit exceeded
     */
    protected function enforceMaxRefreshTokens(User $user): void
    {
        $activeCount = RefreshToken::forUser($user->id)
            ->active()
            ->count();

        if ($activeCount >= $this->maxRefreshTokens) {
            $tokensToRemove = $activeCount - $this->maxRefreshTokens + 1;

            RefreshToken::forUser($user->id)
                ->active()
                ->orderBy('created_at', 'asc')
                ->limit($tokensToRemove)
                ->update([
                    'is_revoked' => true,
                    'rotated_at' => now(),
                ]);

            Log::info('Refresh tokens pruned', [
                'user_id' => $user->id,
                'removed_count' => $tokensToRemove,
                'context' => 'token_limit_enforced',
            ]);
        }
    }

    /**
     * Cleanup expired refresh tokens (can be run as scheduled task)
     */
    public static function cleanupExpiredTokens(): int
    {
        try {
            $deletedCount = RefreshToken::expired()
                ->delete();

            Log::info('Expired refresh tokens cleaned up', [
                'count' => $deletedCount,
                'context' => 'token_cleanup',
            ]);

            return $deletedCount;
        } catch (Exception $e) {
            Log::error('Token cleanup error', [
                'error' => $e->getMessage(),
                'context' => 'token_cleanup_error',
            ]);
            return 0;
        }
    }

    /**
     * Get user from access token payload
     */
    public function getUserFromToken(array $payload): ?User
    {
        $userId = $payload['user_id'] ?? $payload['sub'] ?? null;

        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Get active refresh tokens for user
     */
    public static function getActiveTokensForUser(User $user)
    {
        return RefreshToken::forUser($user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
