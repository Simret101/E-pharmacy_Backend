<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Token</title>
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .error {
            color: #dc3545;
        }
        .message {
            font-size: 1.2em;
            margin: 20px 0;
        }
        .action-link {
            margin-top: 30px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error">Invalid or Expired Token</h1>
        <p class="message">The verification token you provided is invalid or has expired.</p>
        <p class="action-link">
            <a href="{{ route('auth.resend_email_verification_link') }}">Resend Verification Link</a>
        </p>
    </div>
</body>
</html>