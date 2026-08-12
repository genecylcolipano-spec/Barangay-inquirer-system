<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .error-code {
            color: #667eea;
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .error-title {
            color: #2d3748;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .countdown-timer {
            display: inline-block;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px 30px;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            font-family: 'Courier New', monospace;
            min-width: 120px;
        }
        
        .retry-info {
            color: #718096;
            font-size: 14px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f7fafc;
            border-left: 4px solid #667eea;
            text-align: left;
            border-radius: 4px;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .footer-text {
            color: #a0aec0;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⏱️</div>
        
        <div class="error-code">429</div>
        <div class="error-title">Too Many Requests</div>
        
        <div class="error-message">
            You've made too many requests in a short period. Please wait before trying again.
        </div>
        
        <div class="countdown-timer" id="retryTimer">{{ $retryAfter }}</div>
        
        <div class="retry-info">
            <strong>Retry After:</strong> {{ $retryAfter }} second{{ $retryAfter !== 1 ? 's' : '' }}
            <br>
            <small>The timer above will count down. Please come back after it reaches zero.</small>
        </div>
        
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="goBack()">Go Back</button>
            <a href="/" class="btn btn-secondary">Go to Home</a>
        </div>
        
        <div class="footer-text">
            If you continue to experience issues, please contact support.
        </div>
    </div>
    
    <script>
        let timeRemaining = {{ $retryAfter }};
        const timerElement = document.getElementById('retryTimer');
        
        function updateTimer() {
            if (timeRemaining > 0) {
                timeRemaining--;
                timerElement.textContent = timeRemaining;
                setTimeout(updateTimer, 1000);
            } else {
                timerElement.textContent = '0';
                timerElement.style.color = '#48bb78';
            }
        }
        
        function goBack() {
            if (document.referrer) {
                window.location.href = document.referrer;
            } else {
                window.history.back();
            }
        }
        
        updateTimer();
    </script>
</body>
</html>
