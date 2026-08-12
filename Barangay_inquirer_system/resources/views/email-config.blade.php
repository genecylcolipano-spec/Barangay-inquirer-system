<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration - Barangay Inquirer System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .config-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
        }
        .config-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
        }
        .config-item {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-key {
            font-weight: 600;
            color: #667eea;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .config-value {
            color: #333;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-ok {
            background-color: #d4edda;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .instructions {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 30px;
            border-radius: 4px;
        }
        .instructions h5 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .instructions > p, .instructions li {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="config-container">
        <h1 class="config-title">🔧 Email Configuration</h1>

        <div class="config-item">
            <div class="config-key">MAIL_MAILER</div>
            <div class="config-value">
                {{ $config['MAIL_MAILER'] }}
                <span class="status-badge status-ok">✓ Configured</span>
            </div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_HOST</div>
            <div class="config-value">{{ $config['MAIL_HOST'] }}</div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_PORT</div>
            <div class="config-value">{{ $config['MAIL_PORT'] }}</div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_ENCRYPTION</div>
            <div class="config-value">{{ $config['MAIL_ENCRYPTION'] }}</div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_USERNAME</div>
            <div class="config-value">
                @if ($config['MAIL_USERNAME'] === 'not set')
                    <span class="status-badge status-warning">⚠ Not Set</span>
                @else
                    {{ $config['MAIL_USERNAME'] }} <span class="status-badge status-ok">✓ Set</span>
                @endif
            </div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_PASSWORD</div>
            <div class="config-value">
                @if ($config['MAIL_PASSWORD'] === 'not set')
                    <span class="status-badge status-warning">⚠ Not Set</span>
                @else
                    {{ $config['MAIL_PASSWORD'] }} <span class="status-badge status-ok">✓ Set</span>
                @endif
            </div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_FROM_ADDRESS</div>
            <div class="config-value">{{ $config['MAIL_FROM_ADDRESS'] }}</div>
        </div>

        <div class="config-item">
            <div class="config-key">MAIL_FROM_NAME</div>
            <div class="config-value">{{ $config['MAIL_FROM_NAME'] }}</div>
        </div>

        <div class="instructions">
            <h5>📋 How to Set Up Gmail</h5>
            <ol>
                <li>
                    <strong>Enable 2-Step Verification:</strong>
                    <ul>
                        <li>Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                        <li>Enable 2-Step Verification if not already enabled</li>
                    </ul>
                </li>
                <li>
                    <strong>Generate App Password:</strong>
                    <ul>
                        <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a></li>
                        <li>Select "Mail" and "Windows Computer" (or your device)</li>
                        <li>Google will generate a 16-character password</li>
                    </ul>
                </li>
                <li>
                    <strong>Update .env file:</strong>
                    <ul>
                        <li><code>MAIL_USERNAME=your-email@gmail.com</code></li>
                        <li><code>MAIL_PASSWORD=your-16-char-app-password</code> (use the one generated in step 2)</li>
                    </ul>
                </li>
                <li>
                    <strong>Test the connection:</strong> Use the test email form to verify everything works
                </li>
            </ol>
        </div>

        <a href="{{ route('email.test.show') }}" class="back-link">← Back to Email Testing</a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
