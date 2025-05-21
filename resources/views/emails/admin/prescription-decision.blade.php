<!DOCTYPE html>
<html>
<head>
    <title>Prescription Status Update</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <div class="header">
        <h2>Prescription Status Update</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>{{ $message }}</p>
        
        <div class="details">
            <h3>Prescription Details:</h3>
            <ul>
                <li><strong>Order ID:</strong> {{ $order->order_uid }}</li>
                <li><strong>Drug:</strong> {{ $order->drug->name }}</li>
                <li><strong>Quantity:</strong> {{ $order->quantity }}</li>
                <li><strong>Status:</strong> {{ ucfirst($status) }}</li>
            </ul>
            
            <div class="document-preview">
                <h4>Prescription Image:</h4>
                <img src="{{ $prescriptionImageUrl }}" alt="Prescription Image" style="max-width: 100%;">
            </div>
        </div>

        <p>Best regards,</p>
        <p>EPharmacy Team</p>
    </div>
</body>
</html>