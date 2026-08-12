<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Barangay Inquirer System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('auth/auth.css') }}">
</head>
<body>
    <div class="auth-container">
        <!-- Background Decorations -->
        <div class="decorative-blob blob-1"></div>
        <div class="decorative-blob blob-2"></div>
        <div class="decorative-blob blob-3"></div>

        <div class="auth-content">
            <!-- Left Panel: Forgot Password Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                    <h1 class="auth-title">Forgot Password</h1>
                    <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="your.email@example.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login">
                        <span>Send Reset Link</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    <!-- Back to Login Link -->
                    <div class="auth-redirect">
                        <p>Remember your password?
                            <a href="{{ route('login') }}" class="redirect-link">
                                Sign in here
                                <i class="fas fa-sign-in-alt"></i>
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Success/Error Messages -->
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Reset link sent!</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Please check your input</strong>
                            <p>There was an issue with your request.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Illustration -->
            <div class="auth-illustration">
                <div class="illustration-content">
                    <div class="icon-circle primary">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2>Password Recovery</h2>
                    <p class="illustration-text">Don't worry, it happens to the best of us. We'll help you get back in.</p>

                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure Process</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Quick Recovery</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Rate limit error handling and form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.auth-form');
            const submitButton = form.querySelector('button[type="submit"]');
            const email = form.querySelector('#email');
            const alertContainer = document.querySelector('.form-header');

            // Load stored rate limit info from localStorage
            function loadRateLimitState() {
                const stored = localStorage.getItem('password_reset_rate_limit');
                if (stored) {
                    const data = JSON.parse(stored);
                    const now = Date.now();
                    
                    if (data.resetTime > now) {
                        // Rate limit still active
                        showRateLimitState(data);
                        startCountdown(data);
                    } else {
                        // Rate limit expired
                        localStorage.removeItem('password_reset_rate_limit');
                        clearRateLimitState();
                    }
                }
            }

            // Show rate limit state
            function showRateLimitState(data) {
                submitButton.disabled = true;
                submitButton.style.opacity = '0.5';
                submitButton.style.cursor = 'not-allowed';
                email.disabled = true;
                
                const originalHTML = submitButton.innerHTML;
                submitButton.dataset.originalHTML = originalHTML;
                
                updateCountdownDisplay(data.resetTime);
            }

            // Clear rate limit state
            function clearRateLimitState() {
                submitButton.disabled = false;
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
                email.disabled = false;
                
                if (submitButton.dataset.originalHTML) {
                    submitButton.innerHTML = submitButton.dataset.originalHTML;
                }
                
                // Remove any rate limit alerts
                const alerts = alertContainer.querySelectorAll('.alert-rate-limit');
                alerts.forEach(alert => alert.remove());
            }

            // Update countdown display
            function updateCountdownDisplay(resetTime) {
                const remaining = Math.ceil((resetTime - Date.now()) / 1000);
                const minutes = Math.ceil(remaining / 60);
                
                if (remaining > 0) {
                    submitButton.innerHTML = `
                        <span>Wait ${minutes} min${minutes !== 1 ? 's' : ''}</span>
                        <i class="fas fa-hourglass-end"></i>
                    `;
                }
            }

            // Start countdown timer
            function startCountdown(data) {
                const interval = setInterval(() => {
                    const remaining = Math.ceil((data.resetTime - Date.now()) / 1000);
                    
                    if (remaining <= 0) {
                        clearInterval(interval);
                        clearRateLimitState();
                    } else {
                        updateCountdownDisplay(data.resetTime);
                    }
                }, 1000);
            }

            // Show rate limit error alert
            function showRateLimitError(message, retryAfter) {
                // Remove existing alerts
                const existingAlerts = alertContainer.querySelectorAll('.alert');
                existingAlerts.forEach(alert => alert.remove());

                // Create new alert
                const alert = document.createElement('div');
                alert.className = 'alert alert-error alert-rate-limit';
                alert.innerHTML = `
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Too Many Requests</strong>
                        <p>${message}</p>
                        <small style="opacity: 0.8;">Next attempt available in <span class="countdown">${Math.ceil(retryAfter / 60)}</span> minute(s)</small>
                    </div>
                `;
                alertContainer.insertAdjacentElement('afterend', alert);

                // Start countdown in alert
                const countdownEl = alert.querySelector('.countdown');
                const endTime = Date.now() + (retryAfter * 1000);
                
                const countdownInterval = setInterval(() => {
                    const remaining = Math.ceil((endTime - Date.now()) / 1000);
                    if (remaining > 0) {
                        countdownEl.textContent = Math.ceil(remaining / 60);
                    } else {
                        clearInterval(countdownInterval);
                    }
                }, 1000);
            }

            // Handle form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear previous messages
                const existingAlerts = alertContainer.querySelectorAll('.alert');
                existingAlerts.forEach(alert => alert.remove());

                // Check for client-side rate limit
                const stored = localStorage.getItem('password_reset_rate_limit');
                if (stored) {
                    const data = JSON.parse(stored);
                    if (data.resetTime > Date.now()) {
                        const remaining = Math.ceil((data.resetTime - Date.now()) / 1000);
                        showRateLimitError(
                            'Please wait before requesting another password reset.',
                            remaining
                        );
                        return;
                    }
                }

                submitButton.disabled = true;
                const originalHTML = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sending...</span>';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email.value,
                        }),
                    });

                    if (response.ok) {
                        // Success
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.innerHTML = `
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Reset link sent!</strong>
                                <p>Check your email for a password reset link. It's valid for 60 minutes.</p>
                            </div>
                        `;
                        alertContainer.insertAdjacentElement('afterend', alert);

                        // Clear form
                        email.value = '';
                        submitButton.innerHTML = originalHTML;
                        submitButton.disabled = false;

                        // Clear any stored rate limit
                        localStorage.removeItem('password_reset_rate_limit');

                    } else if (response.status === 429) {
                        // Rate limited
                        const data = await response.json();
                        const retryAfter = data.retry_after || 3600;
                        
                        // Store rate limit info
                        const resetTime = Date.now() + (retryAfter * 1000);
                        localStorage.setItem('password_reset_rate_limit', JSON.stringify({
                            email: email.value,
                            resetTime: resetTime,
                        }));

                        // Show error and disable form
                        showRateLimitError(
                            data.message || 'Too many password reset requests. Please try again later.',
                            retryAfter
                        );
                        showRateLimitState({ resetTime });

                        submitButton.innerHTML = originalHTML;

                    } else if (response.status === 422) {
                        // Validation error
                        try {
                            const data = await response.json();
                            // Log detailed error server-side, show generic to user
                            console.debug('Validation error:', data);
                        } catch (e) {
                            console.debug('Could not parse validation error response');
                        }
                        
                        // Generic error message for user
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-error';
                        alert.innerHTML = `
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Request Failed</strong>
                                <p>Please check your email address and try again.</p>
                            </div>
                        `;
                        alertContainer.insertAdjacentElement('afterend', alert);
                        
                        submitButton.innerHTML = originalHTML;
                        submitButton.disabled = false;

                    } else {
                        // Other errors (500, 503, etc.)
                        try {
                            const data = await response.json();
                            // Log detailed error for debugging
                            console.debug('Server error response:', {
                                status: response.status,
                                data: data,
                                timestamp: new Date().toISOString()
                            });
                        } catch (e) {
                            console.debug('Could not parse error response');
                        }
                        
                        // Generic error message for user
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-error';
                        alert.innerHTML = `
                            <i class="fas fa-times-circle"></i>
                            <div>
                                <strong>Something Went Wrong</strong>
                                <p>Please try again later. If the problem persists, contact support.</p>
                            </div>
                        `;
                        alertContainer.insertAdjacentElement('afterend', alert);

                        submitButton.innerHTML = originalHTML;
                        submitButton.disabled = false;
                    }

                } catch (error) {
                    // Network or fetch error
                    console.error('Network/Fetch Error:', {
                        message: error.message,
                        name: error.name,
                        stack: error.stack,
                        timestamp: new Date().toISOString()
                    });
                    
                    // Generic error message for user
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-error';
                    alert.innerHTML = `
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Connection Error</strong>
                            <p>Unable to connect to the server. Please check your internet and try again.</p>
                        </div>
                    `;
                    alertContainer.insertAdjacentElement('afterend', alert);

                    submitButton.innerHTML = originalHTML;
                    submitButton.disabled = false;
                }
            });

            // Add focus states and animations
            document.querySelectorAll('.form-control').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });

            // Smooth scroll for back link
            const backLink = document.querySelector('.back-link');
            if (backLink) {
                backLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = this.href;
                });
            }

            // Load initial rate limit state
            loadRateLimitState();
        });
    </script>
</body>
</html>
