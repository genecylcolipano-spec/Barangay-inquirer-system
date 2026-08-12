<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClerkService
{
    protected ?string $secretKey;
    protected string $baseUrl = 'https://api.clerk.dev/v1';

    public function __construct()
    {
        $this->secretKey = env('CLERK_SECRET_KEY');
    }

    /**
     * Verify a Clerk JWT token
     */
    public function verifyToken(string $token): ?array
    {
        if (!$this->secretKey) {
            Log::error('Clerk secret key not configured');
            return null;
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/tokens/verify", [
                    'token' => $token
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Clerk token verification failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Clerk token verification error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user information from Clerk
     */
    public function getUser(string $userId): ?array
    {
        if (!$this->secretKey) {
            Log::error('Clerk secret key not configured');
            return null;
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/users/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clerk get user error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create or update user in local database from Clerk user data
     */
    public function syncUser(array $clerkUser): ?\App\Models\User
    {
        try {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $clerkUser['email_addresses'][0]['email_address'] ?? $clerkUser['primary_email_address_id']],
                [
                    'name' => $clerkUser['first_name'] . ' ' . $clerkUser['last_name'],
                    'email' => $clerkUser['email_addresses'][0]['email_address'] ?? $clerkUser['primary_email_address_id'],
                    'password' => bcrypt(str_random(32)), // Random password since auth is handled by Clerk
                    'role' => 'resident', // Default role
                    'clerk_id' => $clerkUser['id'], // Store Clerk user ID
                    'email_verified_at' => now(),
                ]
            );
            return $user;
        } catch (\Exception $e) {
            Log::error('Clerk user sync error', [
                'clerk_user_id' => $clerkUser['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get Clerk publishable key for frontend
     */
    public function getPublishableKey(): string
    {
        return env('CLERK_PUBLISHABLE_KEY', '');
    }
}