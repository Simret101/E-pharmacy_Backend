<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3182CE;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #EBF8FF;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        .payment-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            background-color: #3182CE;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Confirmation</h1>
        </div>
        <div class="content">
            <h2>Payment Successfully Processed</h2>
            <p>Dear {{ $user->name }},</p>
            
            <p>Your payment has been successfully processed:</p>
            <p><span class="payment-badge">Paid</span></p>
            
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Drug:</strong> {{ $order->drug->name }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Total Amount:</strong> {{ $order->total_amount }}</p>
            
            <p>Your order will be processed and shipped shortly.</p>
            
            <h3>Thank you for your purchase!</h3>
        </div>
    </div>
</body>
</html>
