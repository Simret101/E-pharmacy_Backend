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
            background-color: {{ $status === 'approved' ? '#3182CE' : '#E53E3E' }};
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
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
            background-color: {{ $status === 'approved' ? '#3182CE' : '#E53E3E' }};
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $status === 'approved' ? 'Order Approved' : 'Order Rejected' }}</h1>
        </div>
        <div class="content">
            <h2>Order Status Update</h2>
            <p>Your order status has been updated:</p>
            <p><span class="status-badge">{{ $status }}</span></p>
            
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Drug:</strong> {{ $order->drug->name }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            
            @if($status === 'approved')
                <p>Your order has been approved and will be processed shortly.</p>
            @else
                <p>Your order has been rejected. Please contact customer support if you have any questions.</p>
            @endif
        </div>
    </div>
</body>
</html>
