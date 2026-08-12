<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - Barangay Inquirer System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('auth/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@clerk/clerk-js@latest/dist/clerk.browser.js"></script>
</head>
<body>
    <div class="auth-container">
        <!-- Background Decorations -->
        <div class="decorative-blob blob-1"></div>
        <div class="decorative-blob blob-2"></div>
        <div class="decorative-blob blob-3"></div>

        <div class="auth-content">
            <!-- Left Panel: Clerk Sign In -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <a href="{{ url('/') }}" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    <h1 class="auth-title">Welcome Back</h1>
                    <p class="auth-subtitle">Sign in to your Barangay Inquirer account</p>
                </div>

                <!-- Clerk Sign In Button -->
                <div class="auth-form">
                    <div id="clerk-sign-in"></div>

                    <!-- Alternative Login Link -->
                    <div class="auth-redirect" style="margin-top: 2rem;">
                        <p>Prefer traditional login?
                            <a href="{{ route('login') }}" class="redirect-link">
                                Sign in with email
                                <i class="fas fa-envelope"></i>
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Authentication failed!</strong>
                            <p>Please check your credentials and try again.</p>
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
                    <h2>Secure Authentication</h2>
                    <p class="illustration-text">Your account is protected with enterprise-grade security from Clerk</p>

                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-lock"></i>
                            <span>End-to-End Encrypted</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Multi-Factor Auth</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-key"></i>
                            <span>Passwordless Options</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Clerk
        const clerkPublishableKey = '{{ $clerkPublishableKey }}';

        if (clerkPublishableKey) {
            const clerk = new Clerk(clerkPublishableKey);

            // Mount the sign-in component
            clerk.load().then(() => {
                const signInDiv = document.getElementById('clerk-sign-in');

                clerk.mountSignIn(signInDiv, {
                    routing: 'path',
                    path: '/clerk/login',
                    afterSignInUrl: '{{ route("clerk.callback") }}',
                    afterSignUpUrl: '{{ route("clerk.callback") }}',
                });

                // Listen for authentication events
                clerk.addListener((event) => {
                    if (event.type === 'signIn' || event.type === 'signUp') {
                        // Get the session token
                        const token = clerk.session?.getToken();

                        if (token) {
                            // Send token to Laravel backend
                            fetch('{{ route("clerk.callback") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                    'Authorization': `Bearer ${token}`
                                },
                                body: JSON.stringify({ token: token })
                            })
                            .then(response => {
                                if (response.ok) {
                                    // Redirect will be handled by the backend
                                    window.location.reload();
                                } else {
                                    console.error('Authentication failed');
                                }
                            })
                            .catch(error => {
                                console.error('Authentication error:', error);
                            });
                        }
                    }
                });
            }).catch(error => {
                console.error('Failed to load Clerk:', error);
                // Fallback to traditional login
                document.getElementById('clerk-sign-in').innerHTML = `
                    <div class="alert alert-error">
                        <p>Clerk authentication is currently unavailable. Please use <a href="{{ route('login') }}">traditional login</a>.</p>
                    </div>
                `;
            });
        } else {
            // No Clerk key configured, show traditional login link
            document.getElementById('clerk-sign-in').innerHTML = `
                <div class="alert alert-info">
                    <p>Clerk authentication is not configured. Please use <a href="{{ route('login') }}">traditional login</a>.</p>
                </div>
            `;
        }
    </script>
</body>
</html>