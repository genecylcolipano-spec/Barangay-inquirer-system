<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Testing - Barangay Inquirer System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .test-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .test-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #ddd;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-test {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: transform 0.2s;
        }
        .btn-test:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .section-divider {
            margin: 30px 0;
            border-top: 2px solid #eee;
            padding-top: 20px;
        }
        .info-text {
            color: #666;
            font-size: 13px;
            margin-top: 8px;
        }
        .config-link {
            text-align: center;
            margin-top: 20px;
        }
        .config-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .config-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="test-title">📧 Email Testing Center</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✓ Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Test Email Section -->
        <div>
            <h3 style="color: #333; margin-bottom: 20px; font-size: 18px; font-weight: 700;">1️⃣ Send Test Email</h3>
            <form action="{{ route('email.test.send') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        placeholder="your@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                    <p class="info-text">📌 We'll send a simple test email to verify Gmail is configured correctly.</p>
                    @error('email')
                        <span class="text-danger" style="font-size: 12px;">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn-test">Send Test Email →</button>
            </form>
        </div>

        <div class="section-divider"></div>

        <!-- Password Reset Email Section -->
        <div>
            <h3 style="color: #333; margin-bottom: 20px; font-size: 18px; font-weight: 700;">2️⃣ Send Password Reset Email</h3>
            <form action="{{ route('email.password-reset.send') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reset_email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="reset_email" 
                        name="email" 
                        class="form-control" 
                        placeholder="your@email.com"
                        required
                    >
                    <p class="info-text">📌 We'll send a password reset email with a clickable reset link.</p>
                </div>
                <button type="submit" class="btn-test">Send Password Reset Email →</button>
            </form>
        </div>

        <div class="config-link">
            <a href="{{ route('email.config') }}">🔧 View Email Configuration</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
