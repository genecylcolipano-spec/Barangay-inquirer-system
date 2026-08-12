<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Barangay Inquirer System</title>
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
            <!-- Left Panel: Login Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <a href="{{ url('/') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    <h1 class="auth-title">Welcome Back</h1>
                    <p class="auth-subtitle">Sign in to your Barangay Inquirer account</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="auth-form">
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
                        >
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <div class="label-row">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <a href="{{ route('password.request') }}" class="forgot-password">Forgot password?</a>
                        </div>
                        <div class="password-input-container">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="••••••••"
                                required
                                onblur="autoHidePassword('password', 'toggle-password')"
                            >
                            <button type="button" class="password-toggle" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Keep me signed in</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login">
                        <span>Sign In</span>
                        <i class="fas fa-sign-in-alt"></i>
                    </button>

                    <!-- Signup Link -->
                    <div class="auth-redirect">
                        <p>Don't have an account? 
                            <a href="{{ route('register') }}" class="redirect-link">
                                Create one now
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </p>
                        <p style="margin-top: 0.5rem;">Or sign in with 
                            <a href="{{ route('clerk.login') }}" class="redirect-link">
                                Clerk Authentication
                                <i class="fas fa-shield-alt"></i>
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Lockout Alert -->
                @if (session('lockout'))
                    <div class="alert alert-error">
                        <i class="fas fa-lock"></i>
                        <div>
                            <strong>Account Temporarily Locked</strong>
                            <p>Too many login attempts. Please try again later.</p>
                            @if (session('minutes'))
                                <p style="font-size: 0.9rem; margin-top: 0.5rem;">Please wait {{ session('minutes') }} minute(s) before trying again.</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Error Messages with Attempts Counter -->
                @if ($errors->any() && !session('lockout'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Login failed!</strong>
                            <p>Please check your credentials and try again.</p>
                            @if (session('remaining_attempts') !== null && session('remaining_attempts') > 0)
                                <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #ff9800;">
                                    <strong>⚠️ Warning:</strong> {{ session('remaining_attempts') }} attempt(s) remaining before your account is locked for 1 minute.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Success Messages -->
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Success!</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Panel: Illustration -->
            <div class="auth-illustration">
                <div class="illustration-content">
                    <div class="icon-circle primary">
                        <i class="fas fa-building"></i>
                    </div>
                    <h2>Barangay Inquirer System</h2>
                    <p class="illustration-text">Secure access to your barangay office documents and services</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Highly Secure</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bolt"></i>
                            <span>Lightning Fast</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Community First</span>
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

        // Smooth scroll for back link
        document.querySelector('.back-link').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
        });
    </script>
</body>
</html>