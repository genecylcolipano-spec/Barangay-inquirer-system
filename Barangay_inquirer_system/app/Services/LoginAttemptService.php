<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class LoginAttemptService
{
    /**
     * Maximum failed login attempts allowed within the time window
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Time window in minutes for tracking failed attempts
     */
    private const TIME_WINDOW = 1;

    /**
     * Lockout duration in minutes
     */
    private const LOCKOUT_DURATION = 1;

    /**
     * Get the cache key for failed attempts
     */
    private function getAttemptsKey(string $email): string
    {
        return "login_attempts_{$email}";
    }

    /**
     * Get the cache key for lockout status
     */
    private function getLockoutKey(string $email): string
    {
        return "login_lockout_{$email}";
    }

    /**
     * Check if the user is currently locked out
     */
    public function isLockedOut(string $email): bool
    {
        return Cache::has($this->getLockoutKey($email));
    }

    /**
     * Get the number of failed attempts for the email
     */
    public function getAttempts(string $email): int
    {
        return Cache::get($this->getAttemptsKey($email), 0);
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $email): int
    {
        $key = $this->getAttemptsKey($email);
        $attempts = $this->getAttempts($email);
        $attempts++;

        // Store the attempt count, decaying after TIME_WINDOW minutes
        Cache::put($key, $attempts, now()->addMinutes(self::TIME_WINDOW));

        // If this is the 5th attempt, lock out the account
        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->lockAccount($email);
        }

        return $attempts;
    }

    /**
     * Lock the account for LOCKOUT_DURATION minutes
     */
    private function lockAccount(string $email): void
    {
        Cache::put($this->getLockoutKey($email), true, now()->addMinutes(self::LOCKOUT_DURATION));
    }

    /**
     * Reset failed attempts for the email
     */
    public function resetAttempts(string $email): void
    {
        Cache::forget($this->getAttemptsKey($email));
        Cache::forget($this->getLockoutKey($email));
    }

    /**
     * Get minutes remaining until lockout expires
     */
    public function getMinutesUntilAvailable(string $email): int
    {
        $lockoutKey = $this->getLockoutKey($email);
        
        if (!Cache::has($lockoutKey)) {
            return 0;
        }

        // Account is locked, return the configured lockout duration
        // This ensures consistent behavior across all cache drivers
        // In practice, since lockout expires after LOCKOUT_DURATION minutes,
        // returning this value is appropriate for the user message
        return self::LOCKOUT_DURATION;
    }
}
