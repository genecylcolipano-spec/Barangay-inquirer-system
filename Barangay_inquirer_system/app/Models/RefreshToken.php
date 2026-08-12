<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'token_hash',
        'ip_address',
        'user_agent',
        'expires_at',
        'rotated_at',
        'is_revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'rotated_at' => 'datetime',
        'is_revoked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
        'token_hash',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get active (non-revoked, non-expired) tokens
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_revoked', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Get expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: Get tokens for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get tokens by hash (for verification)
     */
    public function scopeByHash($query, $hash)
    {
        return $query->where('token_hash', $hash);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return !$this->is_revoked 
            && $this->expires_at > now();
    }

    /**
     * Check if token can be refreshed
     */
    public function canRefresh(): bool
    {
        // Token must not be revoked
        // Token must not have expired
        // Token must not be too old to refresh
        $gracePeriodsExpiration = $this->expires_at
            ->addSeconds(config('jwt.refresh_grace_period'));

        return !$this->is_revoked 
            && $gracePeriodsExpiration > now();
    }

    /**
     * Revoke token
     */
    public function revoke(): void
    {
        $this->update([
            'is_revoked' => true,
            'rotated_at' => now(),
        ]);
    }

    /**
     * Mark token as rotated (replaced with new token)
     */
    public function markAsRotated(): void
    {
        $this->update([
            'rotated_at' => now(),
        ]);
    }

    /**
     * Generate a unique token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    /**
     * Hash token for storage (never store plain token)
     */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Check if this token matches a given token
     */
    public function matchesToken(string $token): bool
    {
        return hash_equals(
            $this->token_hash,
            self::hashToken($token)
        );
    }
}
