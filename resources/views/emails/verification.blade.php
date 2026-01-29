<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }
        .url-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ config('app.name') }}</div>
        <h1>Verify Your Email Address ✉️</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>Thank you for signing up with {{ config('app.name') }}!</p>
        
        <p>To complete your registration and activate your account, please verify your email address by clicking the button below:</p>
        
        <p style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </p>
        
        <div class="warning">
            <strong>⏰ Important:</strong> This verification link will expire in 60 minutes for security reasons.
        </div>
        
        <p>If you're having trouble clicking the verification button, you can also copy and paste the following URL into your web browser:</p>
        
        <div class="url-box">
            {{ $verificationUrl }}
        </div>
        
        <p><strong>Security Notice:</strong> If you did not create an account with {{ config('app.name') }}, no further action is required. You can safely ignore this email.</p>
        
        <p>Once verified, you'll have full access to all features including:</p>
        <ul>
            <li>Complete dashboard functionality</li>
            <li>Geographical data exploration</li>
            <li>Profile customization</li>
            <li>Community features</li>
        </ul>
        
        <p>If you need any assistance, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $user->email }} because you registered for an account.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
