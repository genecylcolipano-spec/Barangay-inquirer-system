<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Barangay Inquirer System</title>
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
            <!-- Left Panel: Signup Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <a href="{{ url('/') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    <h1 class="auth-title">Create Account</h1>
                    <p class="auth-subtitle">Join the Barangay Inquirer community today</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form">
                    @csrf

                    <!-- Full Name Input -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control @error('name') is-invalid @enderror" 
                            placeholder="Juan Dela Cruz"
                            value="{{ old('name') }}"
                            required
                        >
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

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
                        >
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
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
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-check-double"></i> Confirm Password
                        </label>
                        <div class="password-input-container">
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="form-control" 
                                placeholder="Re-enter your password"
                                required
                                onblur="autoHidePassword('password_confirmation', 'toggle-password-confirmation')"
                            >
                            <button type="button" class="password-toggle" id="toggle-password-confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="form-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-signup">
                        <span>Create Account</span>
                        <i class="fas fa-user-plus"></i>
                    </button>

                    <!-- Login Link -->
                    <div class="auth-redirect">
                        <p>Already have an account? 
                            <a href="{{ route('login') }}" class="redirect-link">
                                Sign in here
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Registration failed!</strong>
                            <p>Please check the form and try again.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Illustration -->
            <div class="auth-illustration">
                <div class="illustration-content">
                    <div class="icon-circle accent">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Join Us Today</h2>
                    <p class="illustration-text">Become part of our growing community and access barangay services effortlessly</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-file-alt"></i>
                            <span>Easy Document Access</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bell"></i>
                            <span>Stay Updated</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <span>Community Support</span>
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

        // Back link
        document.querySelector('.back-link').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
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

        document.getElementById('toggle-password-confirmation').addEventListener('click', function() {
            const confirmInput = document.getElementById('password_confirmation');
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

        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>