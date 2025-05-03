<!-- resources/views/paypal.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            margin: 20px auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Processing Your Payment</h2>
        <p>Amount to be paid: ${{ number_format($amount, 2) }}</p>
        <div class="spinner"></div>
        <p>Please wait while we redirect you to PayPal...</p>

        <form id="paypalForm" action="{{ route('paypal.process') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order_id }}">
            <input type="hidden" name="amount" value="{{ $amount }}">
        </form>

        <script>
            window.onload = function() {
                document.getElementById('paypalForm').submit();
            };
        </script>
    </div>
</body>
</html>

