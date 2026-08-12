# Frontend Rate Limit Handling - Test Checklist

## Overview
Testing the password reset email form with 429 rate limit error handling.

## Test Plan

### 1. Successful Email Submission
- [ ] Open `/password/forgot` page
- [ ] Enter valid email
- [ ] Click "Send Password Reset Link"
- **Expected**: Green success alert appears with message "Password reset link sent to your email"
- **Verification**: localStorage does NOT have 'password_reset_rate_limit' entry

### 2. Rate Limiting (3 attempts per hour)
- [ ] Submit same email 4 times within 1 hour
- **Expected on 4th attempt**: 
  - Red error alert appears with shake animation
  - Alert shows: "Too many password reset requests for this email. Please try again in X minutes"
  - Submit button shows countdown: "Try again in 59:59"
  - Email input is disabled
  - localStorage stores: `{email, resetTime}`
  - Countdown timer updates every second

### 3. Countdown Timer Functionality
- [ ] Observe countdown in alert footer and button
- **Expected**: Timer decreases every 1 second
- **Precision**: ±1 second variance acceptable

### 4. Form Disable During Rate Limit
- [ ] Try to submit form while rate limited
- **Expected**: Form does not submit; alert remains visible

### 5. Page Reload Persistence
- [ ] Submit 4 requests to trigger rate limit
- [ ] Observe countdown timer (e.g., 59:45)
- [ ] Refresh page (Ctrl+R or Cmd+R)
- **Expected**: 
  - Countdown resumes from approximately the same time
  - Form remains disabled
  - localStorage state preserved

### 6. Automatic Form Re-enable
- [ ] Wait for rate limit countdown to hit 0:00
- **Expected**: 
  - Countdown stops
  - Alert disappears
  - Submit button re-enabled (full opacity, clickable)
  - Email input re-enabled
  - localStorage entry removed

### 7. Multi-Email Rate Limiting
- [ ] Submit with email1@example.com (4 times) → Rate limited
- [ ] Try another email2@example.com
- **Expected**: email2 works (not rate limited), email1 remains blocked
- **Verification**: Separate localhost entries or rate limit headers checked

### 8. Error Scenarios
- [ ] **Empty email**: Submit without email
  - **Expected**: 422 validation error: "Email field is required"
  
- [ ] **Invalid email**: Submit invalid format
  - **Expected**: 422 validation error: "Email must be a valid address"
  
- [ ] **Network error**: Disconnect internet, submit
  - **Expected**: Network error alert: "Network error. Please check your connection."

### 9. CSS Styling
- [ ] Success alert: Green color and icon visible ✓
- [ ] Error alert: Red color and icon visible ✓  
- [ ] Rate limit alert: Shows shake animation ✓
- [ ] Countdown text: Appears in alert footer ✓
- [ ] Button text: Shows countdown timer ✓

### 10. Browser Compatibility
- [ ] Chrome/Edge: localStorage works, countdown updates ✓
- [ ] Firefox: localStorage works, countdown updates ✓
- [ ] Safari: localStorage works, countdown updates ✓

### 11. JavaScript Disabled Fallback
- [ ] Disable JavaScript in browser
- [ ] Submit form
- **Expected**: Traditional form submission occurs (may show server-side errors)

## Backend Response Verification

### 200 OK Response (Success)
```json
{
  "message": "Password reset link sent to your email address."
}
```

### 429 Too Many Requests Response
```json
{
  "message": "Too many password reset requests for this email.",
  "email": "user@example.com",
  "retry_after": 3598
}
```

Headers:
- `X-RateLimit-Limit: 3`
- `X-RateLimit-Remaining: 0`
- `X-RateLimit-Reset: 1234567890`
- `Retry-After: 3598`

### 422 Validation Error Response
```json
{
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Issues Observed

| Issue | Status | Note |
|-------|--------|------|
| localStorage not persisting | ⬜ | Test and report |
| Countdown not updating | ⬜ | Test and report |
| Alert not showing | ⬜ | Test and report |
| Button disabled not visible | ⬜ | Test and report |
| CSS animation missing | ⬜ | Test and report |

## Test Results Summary

**Date Tested**: _______________  
**Tester**: _______________  
**Browser**: _______________  
**OS**: _______________

**Total Tests**: 11  
**Passed**: ___ / 11  
**Failed**: ___ / 11  
**Notes**: 

```
[Add any notes or issues discovered]
```

## Post-Test Actions

- [ ] Fix any failed tests
- [ ] Update documentation with results
- [ ] Create bug reports for failures
- [ ] Merge to main branch once all tests pass
