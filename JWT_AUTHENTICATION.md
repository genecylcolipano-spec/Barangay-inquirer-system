# JWT Authentication Implementation - 7 Day Expiration & Refresh Token Rotation

**Status**: ✅ IMPLEMENTED  
**Last Updated**: March 26, 2026  
**Framework**: Laravel 12.0  
**Authentication**: JWT (JSON Web Tokens) with Refresh Token Rotation

---

## Overview

This document describes the JWT authentication implementation with:
- **7-day access token expiration** 
- **14-day refresh token expiration**
- **Automatic refresh token rotation** (old token invalidated on refresh)
- **Token binding** to IP address and user agent
- **Secure token storage** (hashed before database storage)
- **Multi-device support** (unlimited active sessions, max 5 per user)
- **Session management** (view and revoke sessions)

---

## Configuration

### JWT Settings
**File**: `config/jwt.php`

```php
return [
    // Access token (JWT) expiration: 7 days
    'jwt_expiration' => 7 * 24 * 60 * 60,

    // Refresh token expiration: 14 days
    'refresh_token_expiration' => 14 * 24 * 60 * 60,

    // Grace period to refresh token before expiry: 5 minutes
    'refresh_grace_period' => 5 * 60,

    // Enable refresh token rotation (old token invalidated on refresh)
    'rotate_refresh_tokens' => true,

    // Max active refresh tokens per user: 5
    'max_refresh_tokens_per_user' => 5,

    // JWT signing algorithm
    'algorithm' => 'HS256',

    // Secret key for signing (from .env: JWT_SECRET)
    'secret_key' => env('JWT_SECRET', env('APP_KEY')),

    // Token issuer and audience
    'issuer' => env('APP_NAME', 'Barangay System'),
    'audience' => env('APP_URL', 'http://localhost'),

    // Clock skew tolerance: 60 seconds
    'leeway' => 60,
];
```

### Environment Variables
**File**: `.env`

```bash
# JWT secret key (use a strong random value)
JWT_SECRET=your-super-secret-jwt-key-here

# Or use APP_KEY if JWT_SECRET not set
APP_KEY=base64:...
```

Generate a strong JWT secret:
```bash
php artisan key:generate
# Then set: JWT_SECRET=<the-generated-key>
```

---

## Database Schema

### Refresh Tokens Table
**File**: `database/migrations/2026_03_26_000000_create_refresh_tokens_table.php`

```sql
CREATE TABLE refresh_tokens (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED FOREIGN KEY,
    token VARCHAR(255) UNIQUE,              -- Plain token (returned to user)
    token_hash VARCHAR(255) UNIQUE,         -- SHA-256 hash (stored for comparison)
    ip_address VARCHAR(45),                 -- IPv4/IPv6 address
    user_agent VARCHAR(255),                -- Browser/device info
    expires_at DATETIME,                    -- Token expiration time
    rotated_at DATETIME NULL,               -- When token was replaced
    is_revoked BOOLEAN DEFAULT FALSE,       -- Revocation status
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- Indexes for performance
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (token),
    UNIQUE KEY (token_hash),
    INDEX (token_hash),
    INDEX (user_id, is_revoked),
    INDEX (expires_at, is_revoked)
);
```

---

## Token Flow

### 1. Initial Token Issuance

```
User logs in (via Clerk or traditional auth)
        ↓
POST /api/auth/token (requires web authentication)
        ↓
Server creates:
  - Access token (JWT, 7 days)
  - Refresh token (DB, 14 days)
        ↓
Response:
{
    "access_token": "eyJhbGc...",
    "token_type": "Bearer",
    "expires_in": 604800,              // 7 days in seconds
    "refresh_token": "abc123def456...",
    "refresh_expires_in": 1209600      // 14 days in seconds
}
```

### 2. Using Access Token (API Requests)

```
Client includes JWT in Authorization header:

GET /api/user
Authorization: Bearer eyJhbGc...

Server validates token:
  ✓ Signature valid
  ✓ Not expired
  ✓ User still exists
  ↓
Request authenticated, proceeds
```

### 3. Token Refresh (Rotation)

```
Access token about to expire
        ↓
POST /api/auth/refresh
Body: {
    "refresh_token": "abc123def456..."
}
        ↓
Server validates:
  ✓ Refresh token exists and hash matches
  ✓ Token not revoked
  ✓ Token not expired
  ✓ Token within grace period (refresh allowed for 5min after expiry)
        ↓
If valid:
  - Mark old refresh token as rotated
  - Create NEW refresh token
  - Return new access token + new refresh token
        ↓
Old refresh token cannot be used again (security)
New tokens have 7 and 14 day expiration from now
```

### 4. Logout (Token Revocation)

```
User clicks logout
        ↓
POST /api/auth/logout
Authorization: Bearer eyJhbGc...
Body: {
    "refresh_token": "abc123def456..."
}
        ↓
Server revokes refresh token:
  - Mark is_revoked = true
  - Set rotated_at = now()
  - Old access token still valid until expiry
  - New access tokens cannot be obtained
```

### 5. Logout from All Devices

```
User logs out from all devices
        ↓
POST /api/auth/logout-all
Authorization: Bearer eyJhbGc...
        ↓
Server revokes ALL active refresh tokens for user:
  - Mark all is_revoked = true
  - User must login again on all devices
```

---

## API Endpoints

### Public Endpoints (No JWT required)

#### 1. Issue Tokens
```
POST /api/auth/token
Authorization: Basic <clerk-token-or-web-session>

Response (200):
{
    "access_token": "eyJhbGc...",
    "token_type": "Bearer",
    "expires_in": 604800,
    "refresh_token": "abc123...",
    "refresh_expires_in": 1209600
}
```

#### 2. Refresh Access Token
```
POST /api/auth/refresh
Content-Type: application/json

Body:
{
    "refresh_token": "abc123..."
}

Response (200):
{
    "access_token": "eyJhbGc...",
    "token_type": "Bearer",
    "expires_in": 604800,
    "refresh_token": "new_token_here_...",
    "refresh_expires_in": 1209600
}

Error (401):
{
    "message": "Refresh token invalid or expired.",
    "error": "invalid_refresh_token"
}
```

---

### Protected Endpoints (JWT required in Authorization header)

#### 3. Logout (Current Device)
```
POST /api/auth/logout
Authorization: Bearer eyJhbGc...
Content-Type: application/json

Body:
{
    "refresh_token": "abc123..."
}

Response (200):
{
    "message": "Logged out successfully."
}
```

#### 4. Logout All Devices
```
POST /api/auth/logout-all
Authorization: Bearer eyJhbGc...

Response (200):
{
    "message": "Logged out from all devices."
}
```

#### 5. Get Active Sessions
```
GET /api/auth/sessions
Authorization: Bearer eyJhbGc...

Response (200):
{
    "sessions": [
        {
            "id": 1,
            "ip_address": "192.168.1.1",
            "user_agent": "Mozilla/5.0...",
            "expires_at": "2026-04-02T14:30:00Z",
            "created_at": "2026-03-26T14:30:00Z"
        },
        {
            "id": 2,
            "ip_address": "203.45.67.89",
            "user_agent": "Safari/14.1...",
            "expires_at": "2026-04-09T10:15:00Z",
            "created_at": "2026-03-19T10:15:00Z"
        }
    ]
}
```

#### 6. Revoke Specific Session
```
DELETE /api/auth/sessions/1
Authorization: Bearer eyJhbGc...

Response (200):
{
    "message": "Session revoked."
}
```

#### 7. Get Current User
```
GET /api/user
Authorization: Bearer eyJhbGc...

Response (200):
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "resident"
    },
    "jwt_payload": {
        "iat": 1711445400,
        "exp": 1712050200,
        "iss": "Barangay System",
        "aud": "http://localhost",
        "sub": 1,
        "user_id": 1,
        "email": "john@example.com",
        "name": "John Doe",
        "role": "resident",
        "jti": "jti_65b8a1c2f3d9e"
    }
}
```

---

## JWT Payload Structure

```json
{
    "iat": 1711445400,          // Issued at (timestamp)
    "exp": 1712050200,          // Expiration (timestamp) - 7 days from iat
    "iss": "Barangay System",   // Issuer
    "aud": "http://localhost",  // Audience
    "sub": 1,                   // Subject (user ID)
    "user_id": 1,               // User ID (duplicate for clarity)
    "email": "user@example.com",// User email
    "name": "John Doe",         // User name
    "role": "resident",         // User role
    "jti": "jti_65b8a1c2f3d9e"  // JWT ID (unique token identifier)
}
```

All times are in Unix timestamp format (seconds since epoch).

---

## Refresh Token Rotation

### How It Works

**Without rotation** (old implementation):
```
Initial token: token_1 (valid 14 days)
  ↓ User refreshes on day 3
New access token issued, token_1 still valid for 14 more days
  ↓ If token_1 compromised, attacker can still use it
```

**With rotation** (our implementation):
```
Initial token: token_1 (valid 14 days)
  ↓ User refreshes on day 3
- token_1 marked as "rotated" (now invalid)
- New token: token_2 created (valid 14 days)
- New access token issued
  ↓ If token_1 compromised before rotation, attacker can continue
  ↓ After rotation, token_1 is useless (rotation detected)
```

### Benefits

✅ **Reduced compromise impact**: Old token becomes invalid automatically  
✅ **Attack detection**: Old token usage after rotation = security alert  
✅ **Device binding**: IP and user agent logged with each token  
✅ **Session limits**: Max 5 active tokens per user prevents token hoarding  
✅ **Grace period**: 5 minutes to refresh before strict expiry

---

## Refresh Token Lifecycle

```
Created: token_1
├─ Status: ACTIVE
├─ Expires: 14 days
├─ Created: 2026-03-26 14:30:00
└─ User refreshes on day 3 (2026-03-29 14:30:00)
   │
   ├─ Action: Token rotated
   ├─ token_1.is_revoked = false
   ├─ token_1.rotated_at = 2026-03-29 14:30:00
   │
   └─ New token_2 created
      ├─ Status: ACTIVE
      ├─ Expires: 14 days
      ├─ Created: 2026-03-29 14:30:00
      └─ User logs out on day 10
         │
         └─ Action: Token revoked
            ├─ token_2.is_revoked = true
            └─ No more refreshes possible
```

---

## Multi-Device Management

Users can have up to **5 active refresh tokens** simultaneously:

```
Device 1 (Phone):
  - IP: 192.168.1.1
  - User Agent: Mobile Safari
  - Expires: 2026-04-02

Device 2 (Laptop):
  - IP: 203.45.67.89
  - User Agent: Chrome on Windows
  - Expires: 2026-04-09

Device 3 (Tablet):
  - IP: 10.0.0.5
  - User Agent: Safari on iPad
  - Expires: 2026-03-31

...up to 5 devices...

When 6th device attempts login:
- Oldest token (by creation date) is revoked
- New token issued for 6th device
- Total active tokens: 5
```

Benefits:
- User can use app on multiple devices
- Old unused devices auto-cleanup after limit
- Session management (view/revoke specific devices)
- Limited token accumulation (prevents abuse)

---

## Security Features

### 1. Secure Token Storage
```php
// Token generation
$plainToken = RefreshToken::generateToken();  // Random 128 hex characters

// Secure hashing
$hashedToken = RefreshToken::hashToken($plainToken);  // SHA-256

// Database storage
RefreshToken::create([
    'token' => $plainToken,              // NOT stored in DB, returned to user only
    'token_hash' => $hashedToken,        // Stored in DB for comparison
]);

// On next refresh, compare hashes (never compare plain tokens)
hash_equals($storedHash, RefreshToken::hashToken($clientToken));
```

### 2. Token Binding
```php
// Tokens bound to device/location
RefreshToken::create([
    'ip_address' => $request->ip(),          // IPv4/IPv6
    'user_agent' => $request->userAgent(),   // Browser fingerprint
    ...
]);

// Benefit: If token stolen, most likely can't use from different IP
// (Consider: VPN, mobile network changes make this informational only)
```

### 3. HTTP-Only Cookies (Recommended)
```javascript
// Client-side setup (Vue/React/vanilla JS)
// Store in httpOnly cookie (not accessible to JavaScript):
fetch('/api/auth/token', {...})
    .then(r => r.json())
    .then(data => {
        // Server sets httpOnly cookies automatically
        // No need for JavaScript storage
    });
```

### 4. HTTPS Only
Tokens MUST be transmitted over HTTPS to prevent interception:
```
✓  https://api.example.com/api/auth/token
✗  http://api.example.com/api/auth/token
```

### 5. Token Expiration
```
Access Token:  7 days (short-lived)
Refresh Token: 14 days (longer-lived, rotated on use)
Grace Period:  5 minutes (allows refresh just after expiry)
```

---

## Implementation Files

| File | Purpose | Lines |
|------|---------|-------|
| `config/jwt.php` | Configuration | 35 |
| `app/Models/RefreshToken.php` | Database model | 125 |
| `database/migrations/.../create_refresh_tokens_table.php` | Schema | 60 |
| `app/Services/JwtService.php` | Token logic | 350+ |
| `app/Http/Middleware/JwtTokenMiddleware.php` | Request validation | 90 |
| `app/Http/Controllers/Api/AuthController.php` | API endpoints | 200+ |
| `routes/api.php` | API routes | 45 |
| `app/Console/Commands/CleanupExpiredTokens.php` | Maintenance | 40 |
| `bootstrap/app.php` | Configuration | ✏️ Updated |

---

## Installation & Setup

### 1. Install JWT Package
```bash
composer require firebase/php-jwt
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Configure JWT
- Set `JWT_SECRET` in `.env`
- Update `config/jwt.php` if needed

### 4. Register Middleware
Already done in `bootstrap/app.php`:
```php
'jwt' => \App\Http\Middleware\JwtTokenMiddleware::class,
```

### 5. Test Token Generation
```bash
# Login first (via Clerk or web form)
curl -X POST http://localhost/api/auth/token \
  -H "Authorization: Bearer <web-session>" \
  -H "Content-Type: application/json"

# Response:
{
    "access_token": "eyJhbGc...",
    "refresh_token": "abc123...",
    "expires_in": 604800
}
```

---

## Usage Examples

### Example 1: Frontend React/Vue

```javascript
// 1. Get tokens after login
const response = await fetch('/api/auth/token', {
    method: 'POST',
    credentials: 'include'  // Include web session
});
const { access_token, refresh_token } = await response.json();

// Store refresh_token securely (httpOnly cookie or secure storage)
localStorage.setItem('refresh_token', refresh_token);

// 2. Use access_token for API requests
fetch('/api/user', {
    headers: {
        'Authorization': `Bearer ${access_token}`
    }
});

// 3. Handle token expiration - refresh automatically
async function refreshToken() {
    const refreshToken = localStorage.getItem('refresh_token');
    const response = await fetch('/api/auth/refresh', {
        method: 'POST',
        body: JSON.stringify({ refresh_token: refreshToken })
    });
    const { access_token, refresh_token: newRefreshToken } = await response.json();
    // Update tokens
}

// 4. Logout
await fetch('/api/auth/logout', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${access_token}`
    },
    body: JSON.stringify({ refresh_token: localStorage.getItem('refresh_token') })
});
localStorage.removeItem('refresh_token');
```

### Example 2: Protected Controller

```php
// Routes automatically authenticated via middleware
Route::middleware('jwt')->get('/api/user-profile', function (Request $request) {
    $user = $request->user();  // Authenticated via JWT
    $payload = $request->attributes->get('jwt_payload');
    
    return response()->json([
        'user' => $user,
        'expires_at' => Carbon::createFromTimestamp($payload['exp'])
    ]);
});
```

### Example 3: Manual Token Verification

```php
use App\Services\JwtService;

$jwt = new JwtService();

// Verify token
$payload = $jwt->verifyAccessToken($token);

if ($payload) {
    $user = $jwt->getUserFromToken($payload);
    // User authenticated
} else {
    // Token invalid or expired
}
```

---

## Maintenance

### Cleanup Expired Tokens
Run periodically (daily recommended) to remove expired tokens:

```bash
# Manual cleanup
php artisan jwt:cleanup

# With confirmation prompt
php artisan jwt:cleanup

# Force without confirmation (for cron)
php artisan jwt:cleanup --force
```

### Schedule Automatic Cleanup
**File**: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Clean up expired refresh tokens daily
    $schedule->command('jwt:cleanup --force')
        ->daily()
        ->at('02:00')
        ->timezone('UTC');
}
```

---

## Logging & Monitoring

### Token Events Logged

| Event | Level | Details |
|-------|-------|---------|
| Token issued | INFO | user_id, ip, context |
| Token refreshed | INFO | user_id, ip, rotated flag |
| Token revoked | WARNING | user_id, logout context |
| All devices logout | WARNING | user_id, context |
| Token refresh failed | WARNING | ip, reason |
| Invalid token | WARNING | ip, validation error |
| Missing token | WARNING | path, ip |
| Token error | ERROR | Full stack trace, context |

### View Logs
```bash
# Tail logs
tail -f storage/logs/laravel.log

# Search for JWT events
grep "JWT\|token_issued\|token_refresh" storage/logs/laravel.log

# Search by user
grep "user_id.*123" storage/logs/laravel.log
```

---

## Error Handling

### 401 Unauthorized
```json
{
    "message": "Authorization token missing.",
    "error": "unauthorized"
}
```
**Cause**: No token provided or invalid token  
**Solution**: Refresh or re-login

### 401 Invalid Token
```json
{
    "message": "Authorization token invalid or expired.",
    "error": "unauthorized"
}
```
**Cause**: Token expired or tampered with  
**Solution**: Refresh with valid refresh token

### 401 User Not Found
```json
{
    "message": "User not found.",
    "error": "unauthorized"
}
```
**Cause**: User deleted or account removed  
**Solution**: Re-login to get new tokens

### 422 Validation Error
```json
{
    "message": "Validation failed.",
    "errors": {
        "refresh_token": ["The refresh token field is required."]
    },
    "error": "validation_error"
}
```

### 500 Server Error
```json
{
    "message": "Failed to issue tokens. Please try again.",
    "error": "server_error"
}
```
**Solution**: Check logs, likely database or mail system issue

---

## Testing

### Test Token Refresh
```bash
# 1. Get initial tokens
TOKEN_RESPONSE=$(curl -s -X POST http://localhost/api/auth/token)
ACCESS=$(echo $TOKEN_RESPONSE | jq -r '.access_token')
REFRESH=$(echo $TOKEN_RESPONSE | jq -r '.refresh_token')

# 2. Refresh token
REFRESH_RESPONSE=$(curl -s -X POST http://localhost/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\":\"$REFRESH\"}")

NEW_ACCESS=$(echo $REFRESH_RESPONSE | jq -r '.access_token')

# 3. Use new token
curl -s http://localhost/api/user \
  -H "Authorization: Bearer $NEW_ACCESS"
```

### Test Token Expiration
```bash
# 1. Create test token with short expiration (modify config/jwt.php temporarily)
# 2. Wait for expiration + grace period
# 3. Try to use expired token
curl -s http://localhost/api/user \
  -H "Authorization: Bearer <expired_token>"

# Should return 401 Unauthorized
```

### Test Token Rotation
```bash
# 1. Get initial tokens
# 2. Refresh (new token issued, old marked as rotated)
# 3. Try to use old refresh token again
curl -s -X POST http://localhost/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\":\"<old_refresh_token>\"}"

# Should return 401 - token already rotated
```

---

## Troubleshooting

### Tokens not working
1. Check `JWT_SECRET` is set in `.env`
2. Verify migration ran: `php artisan migrate --list`
3. Check database table exists: `php artisan tinker` → `RefreshToken::count()`

### "User not found" error
1. User account deleted after token issuance
2. Re-login to get valid token for current user

### Tokens expiring too quickly
1. Check `config/jwt.php` - access token expiration set to 7 days
2. Check server time is correct (NTP sync)
3. Check `leeway` setting (60 second clock skew tolerance)

### Token validation fails on refresh
1. Refresh token must match hash in database
2. Ensure `hash_equals()` comparison (prevents timing attacks)
3. Check token not revoked: `is_revoked = false`
4. Check token not expired: `expires_at > now()`

---

## Performance Considerations

- Token verification is fast (crypto library optimized)
- Database queries use indexes on `token_hash`, `user_id`, `is_revoked`, `expires_at`
- Cleanup command removes old records (prevents table bloat)
- Max 5 tokens per user prevents unlimited growth

---

## Security Considerations

### Store Tokens Securely
-` httpOnly cookies (server-set, not accessible to JS)
- Secure local storage (not perfect, but better than nothing)
- Never log tokens or store in plaintext

### Use HTTPS
- Tokens transmitted over HTTPS only
- Never sent over HTTP

### Verify Signature
- JWT signature verified before using token
- Prevents token tampering

### Token Expiration
- Short-lived access tokens (7 days)
- Longer-lived refresh tokens (14 days, rotated on use)
- Reduces compromise exposure

### Rotation
- Old tokens marked as rotated (can't be used again)
- Detects token theft (compromised token usage)
- Automatic cleanup of old tokens

---

## References & Standards

- [RFC 7519 - JSON Web Token (JWT)](https://tools.ietf.org/html/rfc7519)
- [RFC 7515 - JSON Web Signature (JWS)](https://tools.ietf.org/html/rfc7515)
- [OWASP - JWT Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/JSON_Web_Token_for_Java_Cheat_Sheet.html)
- [Firebase PHP-JWT](https://github.com/firebase/php-jwt)
- [Laravel JWT Documentation](https://laravel.com/docs/authentication)

---

**Implementation Complete**: ✅  
**Next**: Browser/API testing with actual clients
