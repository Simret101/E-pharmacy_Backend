<!DOCTYPE html>
<html>
<head>
    <title>Reset your password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .required {
            color: red;
        }
        .help-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }
        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #0066ff;
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0052cc;
        }
        .error {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Reset your password</h1>
    
    <div class="success-message" id="successMessage">Password has been reset successfully! Redirecting...</div>
    
    <div>Changing password for: {{ $email }}</div>
    
    <form id="resetForm" method="POST" action="{{ url('/api/auth/reset-password') }}">
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="form-group">
            <label for="password">NEW PASSWORD <span class="required">*</span></label>
            <div class="help-text">Between 8 and 72 characters</div>
            <input type="password" id="password" name="password" required minlength="8" maxlength="72">
            <div class="error" id="passwordError"></div>
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">CONFIRM NEW PASSWORD</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <div class="error" id="confirmError"></div>
        </div>
        
        <button type="submit">Change Password</button>
    </form>

    <script>
        document.getElementById('resetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Reset errors
            document.getElementById('passwordError').textContent = '';
            document.getElementById('confirmError').textContent = '';
            document.getElementById('successMessage').style.display = 'none';
            
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            
            // Validate password
            if (password.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters';
                return;
            }
            
            // Validate confirmation
            if (password !== confirmation) {
                document.getElementById('confirmError').textContent = 'Passwords do not match';
                return;
            }
            
            try {
                const response = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: '{{ $email }}',
                        token: '{{ $token }}',
                        password: password,
                        password_confirmation: confirmation
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Show success message
                    document.getElementById('successMessage').style.display = 'block';
                    // Disable the form
                    document.getElementById('resetForm').style.opacity = '0.5';
                    document.getElementById('resetForm').style.pointerEvents = 'none';
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = '/'; // Redirect to welcome page
                    }, 2000);
                } else {
                    alert(data.message || 'An error occurred while resetting your password');
                }
            } catch (error) {
                alert('An error occurred while resetting your password');
            }
        });
    </script>
</body>
</html> 