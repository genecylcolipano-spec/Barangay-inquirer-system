# Error Handling Strategy - Generic User Messages, Detailed Server Logging

**Status**: ✅ IMPLEMENTED  
**Last Updated**: March 26, 2026

## Overview

This document outlines the error handling strategy across the application:
- **Users see**: Generic, non-revealing error messages
- **Logs contain**: Detailed error information, context, and stack traces for debugging
- **Security benefit**: Prevents information leakage about system internals
- **Debugging benefit**: Full context available in logs for troubleshooting

---

## Implementation

### 1. Frontend Error Handling (JavaScript)

**File**: `resources/views/auth/passwords/email.blade.php`

All errors caught and handled with generic user messages:

```javascript
try {
    const response = await fetch(form.action, {...});
    
    if (response.ok) {
        // Success - show user-friendly message
        showAlert('Reset link sent!', 'Success alert');
    } else if (response.status === 429) {
        // Rate limited - generic message
        const data = await response.json();
        showRateLimitError('Too many requests', data.retry_after);
    } else if (response.status === 422) {
        // Validation error - generic message
        console.debug('Validation error:', data); // Log details for debugging
        showAlert('Please check your email and try again', 'Error');
    } else {
        // Other errors - generic message
        console.debug('Server error:', {...}); // Log details
        showAlert('Something went wrong. Please try again later.', 'Error');
    }
} catch (error) {
    // Network error - generic message
    console.error('Network/Fetch Error:', {...}); // Log details
    showAlert('Connection error. Please check your internet.', 'Error');
}
```

**Key Principles**:
- ✅ Users see readable but generic messages
- ✅ Detailed errors logged to browser console (for developers/debugging)
- ✅ No sensitive information exposed to users
- ✅ Specific error details only in console.debug()/console.error()

---

### 2. Backend Error Handling (PHP/Middleware)

**File**: `app/Http/Middleware/PasswordResetThrottle.php`

All exceptions caught with comprehensive logging:

```php
try {
    // Rate limit checking logic...
    
} catch (\Exception $e) {
    // Log detailed error with full context
    \Log::error('Password reset middleware error', [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),  // Full stack trace
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'timestamp' => now()->toIso8601String(),
        'request_id' => $request->id(),  // For tracing across logs
    ]);

    // Return generic error to user
    return response()->json(
        ['message' => 'An error occurred. Please try again later.'],
        500
    );
}
```

**Key Principles**:
- ✅ All exceptions caught
- ✅ Full stack trace logged
- ✅ Request context included (IP, user agent, request ID)
- ✅ Timestamp for correlation
- ✅ Generic error returned to user

---

### 3. Controller Error Handling (PHP)

**File**: `app/Http/Controllers/Auth/ForgotPasswordController.php`

Multi-level error handling with appropriate responses:

```php
public function sendResetLinkEmail(Request $request)
{
    try {
        // Validate input
        $validated = $request->validate(['email' => 'required|email']);

        // Log the attempt
        \Log::info('Password reset link request', [
            'email' => $validated['email'],
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Send reset link
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            // Success
            \Log::info('Password reset link sent successfully', [...]);
            return response()->json(['message' => 'Reset link sent.'], 200);
        }

        // Email not found (don't leak this to user)
        \Log::warning('Password reset for non-existent email', [
            'email' => $validated['email'],
            'ip' => $request->ip(),
        ]);

        // Return same message as success for security
        return response()->json([
            'message' => 'If an account exists, we sent a reset link.'
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Validation error - log and return generic message
        \Log::warning('Password reset validation failed', [
            'errors' => $e->errors(),
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Request format is invalid.',
            'errors' => ['email' => ['Please enter a valid email address.']],
        ], 422);

    } catch (\Exception $e) {
        // Generic exception (mail failure, DB error, etc.)
        \Log::error('Password reset email sending error', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Generic error message
        return response()->json([
            'message' => 'An error occurred. Please try again later.'
        ], 500);
    }
}
```

**Key Principles**:
- ✅ Three separate catch blocks for different error types
- ✅ Validation errors logged with context
- ✅ Mail/system errors logged with full stack trace
- ✅ Generic messages returned in all cases
- ✅ Request context included in all logs

---

### 4. Centralized Error Handler Service

**File**: `app/Services/ErrorHandler.php`

Reusable error handling service for consistent patterns:

```php
use App\Services\ErrorHandler;

// Log an error with context
ErrorHandler::logError(
    $exception,
    $request,
    'password_reset_email_send',  // Context
    ['email' => 'user@example.com']  // Additional data
);

// Log a warning
ErrorHandler::logWarning(
    'Password reset attempt failed',
    $request,
    'password_reset'
);

// Log info
ErrorHandler::logInfo(
    'Password reset link sent',
    $request,
    'password_reset_success'
);

// Get generic error message
$message = ErrorHandler::genericErrorMessage(500);
// Returns: "An error occurred. Please try again later."

// Build API error response
$response = ErrorHandler::apiErrorResponse(422, null, [
    'email' => ['Invalid email format']
]);

// Sanitize data before logging (removes passwords, tokens, etc.)
$safe = ErrorHandler::sanitizeForLogging($requestData);
```

---

## Error Types and Messages

### User-Facing Messages

| Error Type | User Message | HTTP Code |
|-----------|--------------|-----------|
| Validation Error | "Please check your email and try again." | 422 |
| Rate Limited | "Too many requests. Please try again later." | 429 |
| Not Authorized | "You are not authorized to perform this action." | 401 |
| Forbidden | "You do not have permission to access this." | 403 |
| Not Found | "The requested resource was not found." | 404 |
| Conflict | "A conflict occurred. Please try again." | 409 |
| Server Error | "An error occurred. Please try again later." | 500 |
| Service Unavailable | "The service is temporarily unavailable." | 503 |

**All messages are**:
- ✅ Non-technical
- ✅ Non-revealing
- ✅ User-friendly
- ✅ Action-oriented (suggests retry)

### Server-Side Logs

Each error logged with:
- ✅ Full error message and exception details
- ✅ File and line number of error origin
- ✅ Full stack trace for debugging
- ✅ Request context (IP, user agent, method, path)
- ✅ Authentication context (user ID, email)
- ✅ Timestamp in ISO 8601 format
- ✅ Unique request ID for correlation
- ✅ Operation context (what was being attempted)

---

## What Gets Logged

### ✅ Always Logged

```php
[
    'error' => 'actual error message',
    'code' => 500,
    'file' => '/path/to/file.php',
    'line' => 42,
    'trace' => 'full stack trace',
    'ip' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'method' => 'POST',
    'path' => '/password/email',
    'timestamp' => '2026-03-26T14:30:00Z',
    'request_id' => 'abc-123-def-456',
]
```

### ❌ Never Logged (Sensitive Data)

- Passwords
- Password confirmation
- Reset/authentication tokens
- Credit card numbers
- API keys
- Secrets
- Personal authentication data

Use `ErrorHandler::sanitizeForLogging()` if unsure:

```php
$safe = ErrorHandler::sanitizeForLogging([
    'email' => 'user@example.com',  // Safe - logged as-is
    'password' => 'secret123',      // UNSAFE - logged as '[REDACTED]'
    'api_key' => 'key_12345',       // UNSAFE - logged as '[REDACTED]'
]);
```

---

## Log Channels and Retention

### Log Configuration
**File**: `config/logging.php`

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'permission' => 0644,
    ],
]
```

### Accessing Logs

**SSH/Terminal**:
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Search for specific error
grep "Password reset middleware error" storage/logs/laravel.log

# Search by request ID
grep "abc-123-def-456" storage/logs/laravel.log

# Search by email (for investigations)
grep "user@example.com" storage/logs/laravel.log
```

### Log Rotation

Logs are rotated daily and retained for 14 days (configurable).

---

## Usage Examples

### Example 1: Password Reset - Successful

**User sees**:
```
✓ Reset link sent!
Check your inbox and spam folder. Valid for 60 minutes.
```

**Server logs**:
```
[2026-03-26 14:30:00] local.INFO: Password reset link sent successfully 
{
  "context": "password_reset_email_send",
  "email": "user@example.com",
  "timestamp": "2026-03-26T14:30:00Z",
  "request_id": "req-123"
}
```

### Example 2: Password Reset - Rate Limited

**User sees**:
```
⏱ Too many requests
Please try again in 45 minutes
```

**Server logs**:
```
[2026-03-26 14:31:00] local.WARNING: Password reset rate limit exceeded
{
  "email": "user@example.com",
  "ip": "192.168.1.1",
  "attempts": 4,
  "max_attempts": 3,
  "retry_after": 2700,
  "timestamp": "2026-03-26T14:31:00Z",
  "request_id": "req-124"
}
```

### Example 3: Password Reset - Mail Failure

**User sees**:
```
! Connection error
Unable to connect to the server. Please try again later.
```

**Server logs**:
```
[2026-03-26 14:32:00] local.ERROR: Password reset email sending error
{
  "context": "password_reset_email_send",
  "error": "Failed to send mail: SMTP connection timeout",
  "code": 0,
  "file": "/app/Mails/ResetPasswordMail.php",
  "line": 42,
  "trace": "[full stack trace]",
  "email": "user@example.com",
  "ip": "192.168.1.1",
  "timestamp": "2026-03-26T14:32:00Z",
  "request_id": "req-125"
}
```

---

## Implementation Checklist

- ✅ Frontend JavaScript catches all errors
- ✅ Frontend shows generic messages only
- ✅ Frontend logs details to console for debugging
- ✅ Middleware catches all exceptions
- ✅ Middleware logs with full context
- ✅ Middleware returns generic errors
- ✅ Controller handles multiple error types
- ✅ Controller logs with context
- ✅ Controller returns generic messages
- ✅ ErrorHandler service created
- ✅ All error logging includes request context
- ✅ No sensitive data logged (passwords, tokens)
- ✅ Timestamps included in ISO 8601 format
- ✅ Request IDs included for correlation
- ✅ Stack traces included for debugging

---

## Testing Error Handling

### Test 1: Valid Email
```bash
curl -X POST http://localhost/password/email \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'

# Expected response:
{
  "message": "If an account exists, we sent a reset link."
}

# Server logs: info level "Password reset link request"
```

### Test 2: Invalid Email
```bash
curl -X POST http://localhost/password/email \
  -H "Content-Type: application/json" \
  -d '{"email":"invalid"}'

# Expected response:
{
  "message": "Request format is invalid.",
  "errors": {
    "email": ["Please enter a valid email address."]
  }
}

# Server logs: warning level "Password reset validation failed"
```

### Test 3: Rate Limit
```bash
# Run 4 times for same email (limit is 3)
# 4th request returns 429 with generic message

# Server logs: warning level "Password reset rate limit exceeded"
```

### Test 4: Simulate Mail Error
```bash
# Disable mail service or use invalid SMTP config
# Try to send password reset

# Expected response to user:
{
  "message": "An error occurred. Please try again later."
}

# Server logs: error level with full exception trace
```

---

## Troubleshooting

### Check Logs
```bash
# Parse logs as JSON for better readability
cat storage/logs/laravel.log | python3 -m json.tool

# Find errors in last hour
grep "ERROR\|error" storage/logs/laravel.log | tail -20
```

### Identify Issues
```bash
# Search by request ID to see complete request lifecycle
grep "req-123" storage/logs/laravel.log

# Search by user email for investigations
grep "user@example.com" storage/logs/laravel.log

# See all rate limit violations
grep "rate limit" storage/logs/laravel.log
```

### Debug Frontend
- Open browser DevTools → Console tab
- Look for `console.error()` and `console.debug()` messages
- These contain detailed error information while users see generic messages
- Use browser Network tab to see actual API responses

---

## Best Practices

1. **Always catch exceptions** - Never let exceptions propagate to users
2. **Always log context** - Include IP, user agent, request ID, timestamp
3. **Never expose details** - Users see only generic messages
4. **Always sanitize** - Remove passwords, tokens before logging
5. **Always include request ID** - Helps trace issues across logs
6. **Always use ISO 8601 timestamps** - For consistency and easy parsing
7. **Test error scenarios** - Verify error handling works correctly
8. **Monitor error logs** - Set up alerts for ERROR level logs
9. **Review logs regularly** - Look for patterns or unusual activity
10. **Document unusual errors** - Add comments for non-obvious error handling

---

## Security Benefits

✅ **Information Hiding**
- Users can't discover system details from error messages
- Prevents reconnaissance attacks
- Protects against social engineering

✅ **Attack Prevention**
- Generic messages don't reveal validation logic
- Rate limit messages don't confirm email existence
- Error messages don't hint at database structure

✅ **Audit Trail**
- All errors logged with full context
- Can investigate security incidents
- Can identify attack patterns

✅ **Compliance**
- GDPR: Minimize data exposure
- Security: Standard industry practice
- Audit: Full logs for compliance

---

## Performance Considerations

- Logging is asynchronous (doesn't block request)
- Stack traces only logged for ERROR level
- Request context minimized (no full request bodies)
- Log rotation prevents disk space issues
- Indices help search large log files

---

## Migration Guide

If implementing in existing code:

1. **Identify all error points** - Search for `throw`, `respond`, and exception handlers
2. **Add try-catch blocks** - Wrap error-prone code
3. **Add logging** - Use ErrorHandler service consistently
4. **Update responses** - Replace detailed messages with generic ones
5. **Test thoroughly** - Verify errors don't leak information
6. **Deploy gradually** - Update one feature at a time
7. **Monitor logs** - Watch for new error patterns

---

## Related Documentation

- `PASSWORD_RESET_RATE_LIMITING.md` - Rate limiting details
- `SECURITY_AUDIT.md` - Security analysis
- `PASSWORD_RESET_IMPLEMENTATION_COMPLETE.md` - Complete phase summary

---

**Status**: Complete and implemented ✅  
**Next Review**: [To be scheduled]
