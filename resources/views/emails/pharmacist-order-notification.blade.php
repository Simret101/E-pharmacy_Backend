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
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3182CE;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
        }
        .button:hover {
            background-color: #4299E1;
        }
        .image-container {
            max-width: 100%;
            margin: 20px 0;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Order for Review</h1>
        </div>
        <div class="content">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Drug:</strong> {{ $order->drug->name }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Total Amount:</strong> {{ $order->total_amount }}</p>
            
            <div class="image-container">
                <img src="{{ $prescriptionImageUrl }}" alt="Prescription Image">
            </div>

            <p>Please review the prescription and take appropriate action.</p>

            <a href="{{ route('orders.approve', $order->id) }}" class="button">Approve Order</a>
            <a href="{{ route('orders.reject', $order->id) }}" class="button">Reject Order</a>
        </div>
    </div>
</body>
</html>
