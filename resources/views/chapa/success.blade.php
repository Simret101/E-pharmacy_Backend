<!-- resources/views/chapa/success.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; font-size: 24px; }
    </style>
</head>
<body>
    <h1 class="success">Payment Successful!</h1>
    <p>Order #{{ $order->id }} has been paid.</p>
    <p>Amount: {{ $payment->amount }} {{ $payment->currency }}</p>
    <p>Transaction ID: {{ $payment->payment_id }}</p>
   
</body>
</html>