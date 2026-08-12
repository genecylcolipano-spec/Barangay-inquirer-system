<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Barangay Inquirer System</title>
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
            <!-- Left Panel: Reset Password Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                    <h1 class="auth-title">Reset Password</h1>
                    <p class="auth-subtitle">Enter your new password below</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

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
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                        >
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> New Password
                        </label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Create a strong password"
                                required
                                onkeyup="checkPasswordStrength(this.value)"
                                onblur="autoHidePassword('password', 'toggle-password')"
                            >
                            <button type="button" class="password-toggle" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror

                        <!-- Password Strength Indicator -->
                        <div id="password-strength" style="display: none; margin-top: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div id="strength-bar" style="flex: 1; height: 6px; background-color: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                    <div id="strength-fill" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                                </div>
                                <span id="strength-text" style="font-size: 0.85rem; font-weight: 600; min-width: 50px;"></span>
                            </div>

                            <!-- Password Requirements Checklist -->
                            <div style="font-size: 0.85rem;">
                                <div id="req-length" style="display: flex; align-items: center; gap: 6px; margin-bottom: 5px; color: #999;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <span>At least 8 characters</span>
                                </div>
                                <div id="req-uppercase" style="display: flex; align-items: center; gap: 6px; margin-bottom: 5px; color: #999;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <span>One uppercase letter (A-Z)</span>
                                </div>
                                <div id="req-lowercase" style="display: flex; align-items: center; gap: 6px; margin-bottom: 5px; color: #999;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <span>One lowercase letter (a-z)</span>
                                </div>
                                <div id="req-number" style="display: flex; align-items: center; gap: 6px; margin-bottom: 5px; color: #999;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <span>One number (0-9)</span>
                                </div>
                                <div id="req-special" style="display: flex; align-items: center; gap: 6px; color: #999;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <span>One special character (!@#$%^&*)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="form-group">
                        <label for="password-confirm" class="form-label">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="password-confirm"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="••••••••"
                                required
                                onblur="autoHidePassword('password-confirm', 'toggle-password-confirm')"
                            >
                            <button type="button" class="password-toggle" id="toggle-password-confirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login">
                        <span>Reset Password</span>
                        <i class="fas fa-key"></i>
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

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Password reset failed!</strong>
                            <p>Please check your input and try again.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Illustration -->
            <div class="auth-illustration">
                <div class="illustration-content">
                    <div class="icon-circle primary">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2>Secure Reset</h2>
                    <p class="illustration-text">Your new password will be securely updated. Choose something strong and memorable.</p>

                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Verified Link</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-lock"></i>
                            <span>Encrypted</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Account Protected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Password toggle functionality
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('toggle-password-confirm').addEventListener('click', function() {
            const confirmInput = document.getElementById('password-confirm');
            const icon = this.querySelector('i');
            
            if (confirmInput.type === 'password') {
                confirmInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Auto-hide password when focus is lost
        function autoHidePassword(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('i');
            
            // Only hide if it's currently visible
            if (input.type === 'text') {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check password strength
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('password-strength');
            const strengthBar = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');

            if (!password) {
                strengthDiv.style.display = 'none';
                return;
            }

            strengthDiv.style.display = 'block';

            // Check requirements
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/.test(password)
            };

            // Update requirement indicators
            updateRequirementUI('length', requirements.length);
            updateRequirementUI('uppercase', requirements.uppercase);
            updateRequirementUI('lowercase', requirements.lowercase);
            updateRequirementUI('number', requirements.number);
            updateRequirementUI('special', requirements.special);

            // Calculate strength score
            let metRequirements = Object.values(requirements).filter(req => req).length;
            let strength = (metRequirements / 5) * 100;

            // Update strength bar
            strengthBar.style.width = strength + '%';

            // Set color and label
            if (strength < 40) {
                strengthBar.style.backgroundColor = '#e74c3c';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#e74c3c';
            } else if (strength < 60) {
                strengthBar.style.backgroundColor = '#f39c12';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#f39c12';
            } else if (strength < 80) {
                strengthBar.style.backgroundColor = '#3498db';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#3498db';
            } else {
                strengthBar.style.backgroundColor = '#27ae60';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#27ae60';
            }
        }

        // Update requirement UI
        function updateRequirementUI(requirement, met) {
            const reqElement = document.getElementById('req-' + requirement);
            if (reqElement) {
                if (met) {
                    reqElement.style.color = '#27ae60';
                    const icon = reqElement.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-circle');
                        icon.classList.add('fa-check-circle');
                    }
                } else {
                    reqElement.style.color = '#999';
                    const icon = reqElement.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-check-circle');
                        icon.classList.add('fa-circle');
                    }
                }
            }
        }

        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password-confirm');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);

        // Smooth scroll for back link
        document.querySelector('.back-link').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
        });
    </script>
</body>
</html>
