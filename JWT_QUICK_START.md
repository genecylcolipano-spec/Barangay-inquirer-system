# JWT Implementation - Quick Start Guide

**Setup Time**: ~5 minutes  
**Requirements**: Laravel 12.0+, PHP 8.2+

---

## Installation Steps

### 1. Install Composer Package
```bash
composer require firebase/php-jwt
```

### 2. Run Migration
```bash
php artisan migrate
```

This creates the `refresh_tokens` table.

### 3. Configure JWT Secret
**Edit `.env`**:
```bash
# Use app key as JWT secret (already generates on `php artisan key:generate`)
JWT_SECRET=base64:your_app_key_here

# Or generate new secret
php artisan key:generate
```

### 4. Verify Installation
```bash
# Check migration
php artisan migrate --list | grep refresh_tokens

# Test in tinker
php artisan tinker
> \App\Models\RefreshToken::count()
=> 0

> exit
```

✅ Ready to use!

---

## API Usage

### Get Tokens (After Login)
```bash
curl -X POST http://localhost/api/auth/token \
  -H "Authorization: Bearer <web-session>" \
  -H "Content-Type: application/json"
```

**Response**:
```json
{
    "access_token": "eyJhbGc...",
    "token_type": "Bearer",
    "expires_in": 604800,
    "refresh_token": "abc123...",
    "refresh_expires_in": 1209600
}
```

### Use Token for Requests
```bash
curl -X GET http://localhost/api/user \
  -H "Authorization: Bearer eyJhbGc..."
```

### Refresh Token
```bash
curl -X POST http://localhost/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":"abc123..."}'
```

### Logout
```bash
curl -X POST http://localhost/api/auth/logout \
  -H "Authorization: Bearer eyJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":"abc123..."}'
```

### View Sessions
```bash
curl -X GET http://localhost/api/auth/sessions \
  -H "Authorization: Bearer eyJhbGc..."
```

### Logout All Devices
```bash
curl -X POST http://localhost/api/auth/logout-all \
  -H "Authorization: Bearer eyJhbGc..."
```

---

## Configuration

### Default Settings
| Setting | Value | Note |
|---------|-------|------|
| Access Token TTL | 7 days | JWT expiration |
| Refresh Token TTL | 14 days | DB token expiration |
| Grace Period | 5 min | Allow refresh after expiry |
| Token Rotation | Enabled | Old token invalidated on refresh |
| Max Tokens/User | 5 | Prevents token hoarding |
| Algorithm | HS256 | HMAC with SHA-256 |

### Modify Settings
**Edit `config/jwt.php`**:
```php
return [
    'jwt_expiration' => 7 * 24 * 60 * 60,  // Change this
    'refresh_token_expiration' => 14 * 24 * 60 * 60,
    ...
];
```

---

## Protect Routes

### Via Middleware
```php
// In routes/api.php
Route::middleware('jwt')->group(function () {
    Route::get('/user', function () {
        return auth()->user();
    });
});
```

### Via Controller
```php
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function profile()
    {
        return auth()->user();
    }
}
```

### Get JWT Payload
```php
$payload = request()->attributes->get('jwt_payload');
$expiresAt = \Carbon\Carbon::createFromTimestamp($payload['exp']);
```

---

## Testing

### Create Test User
```php
php artisan tinker

> $user = \App\Models\User::first();
> auth()->setUser($user);
> exit
```

### Issue Tokens
```bash
# In browser console after logging in
fetch('/api/auth/token', {
    method: 'POST',
    credentials: 'include'
}).then(r => r.json()).then(console.log)
```

### Manual Token Verification
```php
php artisan tinker

> $jwt = new \App\Services\JwtService();
> $token = 'eyJhbGc...';  
> $payload = $jwt->verifyAccessToken($token);
> $payload['user_id']
=> 1

> exit
```

---

## Maintenance

### Cleanup Expired Tokens
```bash
# Manual
php artisan jwt:cleanup

# Force (no confirmation)
php artisan jwt:cleanup --force
```

### Schedule Automatic Cleanup
**Edit `app/Console/Kernel.php`**:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('jwt:cleanup --force')
        ->daily()
        ->at('02:00')
        ->timezone('UTC');
}
```

---

## Monitoring

### View Active Tokens
```php
php artisan tinker

> \App\Models\RefreshToken::active()->count()
=> 15

> \App\Models\RefreshToken::where('user_id', 1)->active()->get()

> exit
```

### View Token Activity
```bash
# Watch token logs
tail -f storage/logs/laravel.log | grep "token_"
```

---

## Security Checklist

- ✅ JWT_SECRET set in .env (strong random value)
- ✅ HTTPS enabled in production
- ✅ Tokens stored in httpOnly cookies (not localStorage)
- ✅ Refresh token rotation enabled
- ✅ Token expiration set (7 days access, 14 days refresh)
- ✅ Cleanup command scheduled (daily)
- ✅ Error messages don't leak details
- ✅ Token bound to IP/user agent (for audit)

---

## Common Tasks

### Revoke All Tokens for User (Security Breach)
```php
\App\Services\JwtService::class;

$user = \App\Models\User::find($userId);
(new JwtService())->revokeAllRefreshTokens($user);
```

### Get User's Active Sessions
```php
$user = \App\Models\User::find($userId);
$sessions = \App\Models\RefreshToken::forUser($user->id)->active()->get();
```

### Check If Token is Rotated
```php
$token = \App\Models\RefreshToken::find($id);
$token->rotated_at  // NULL = not rotated, timestamp = rotated
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| `Class not found` | Run `composer require firebase/php-jwt` |
| `Table not found` | Run `php artisan migrate` |
| `Invalid token` | Check JWT_SECRET matches issuer |
| `User not found` | User deleted; re-login required |
| `Token expired` | Use refresh endpoint |
| `Refresh failed` | Token revoked or too old (>14 days + grace) |

---

## Next Steps

1. ✅ Install package and run migration
2. ✅ Set JWT_SECRET in .env
3. ✅ Test `/api/auth/token` endpoint
4. ✅ Test token refresh
5. ✅ Protect your API routes
6. ✅ Schedule token cleanup
7. ✅ Monitor logs

---

**Ready to use!** 🚀

See `JWT_AUTHENTICATION.md` for complete documentation.
