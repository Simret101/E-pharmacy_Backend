<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email Address</title>
</head>
<body>
    <h2>Verify Your Email Address</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Thank you for registering with EPharmacy. Please click the button below to verify your email address.</p>
    
    <p>
        <a href="{{ url('/api/verify-email/' . $token) }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Verify Email Address
        </a>
    </p>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <p>This verification link will expire in 24 hours.</p>
    
    <p>Best regards,<br>EPharmacy System</p>
</body>
</html> 