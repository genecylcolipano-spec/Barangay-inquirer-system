<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configure JWT tokens for API authentication with refresh token rotation
    |
    */

    // Main JWT token expiration (7 days in seconds)
    'jwt_expiration' => 7 * 24 * 60 * 60,

    // Refresh token expiration (14 days in seconds)
    // Must be longer than access token
    'refresh_token_expiration' => 14 * 24 * 60 * 60,

    // Refresh token grace period (5 minutes)
    // Allow refreshing tokens within this period before main expiration
    'refresh_grace_period' => 5 * 60,

    // Rotation settings
    'rotate_refresh_tokens' => true,

    // Maximum number of active refresh tokens per user
    // Prevents unlimited token accumulation
    'max_refresh_tokens_per_user' => 5,

    // Algorithm for token signing
    'algorithm' => 'HS256',

    // Token signing key (use JWT_SECRET from .env)
    'secret_key' => env('JWT_SECRET', env('APP_KEY')),

    // Issuer claim (iss) - identifies the principal that issued the JWT
    'issuer' => env('APP_NAME', 'Barangay System'),

    // Audience claim (aud) - identifies the recipients that the JWT is intended for
    'audience' => env('APP_URL', 'http://localhost'),

    // Leeway for token validation (in seconds)
    // Accounts for clock skew between servers
    'leeway' => 60,
];
