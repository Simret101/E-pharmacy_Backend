<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Token</title>
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Password Reset Token</h1>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <p class="text-gray-600 mb-4">
                You have received this message because we received a password reset request for your account.
            </p>
            <div class="bg-blue-100 p-4 rounded mb-4">
                <p class="text-xl font-semibold text-blue-800">
                    Your Reset Token: <span class="font-bold">{{ $token }}</span>
                </p>
            </div>
            <p class="text-gray-600 mb-4">
                This token will expire in 60 minutes. Please use it to reset your password.
            </p>
            <p class="text-gray-600">
                If you did not request a password reset, no further action is required.
            </p>
        </div>
    </div>
</body>
</html>