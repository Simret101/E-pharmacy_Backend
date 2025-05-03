<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <p>Hello {{ $user->name }},</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Click the button below to reset your password:</p>
    <p>
        <a href="{{ $resetUrl }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Reset Password
        </a>
    </p>
    <p>This password reset link will expire in {{ $expire }} minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html> 