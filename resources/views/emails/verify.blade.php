<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email Address</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Please click the button below to verify your email address.</p>
    <a href="{{ $verificationUrl }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Verify Email Address
    </a>
    <p>If you did not create an account, no further action is required.</p>
    <p>Regards,<br>EPharmacy Team</p>
</body>
</html> 