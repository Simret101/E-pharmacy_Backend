<!DOCTYPE html>
<html>
<head>
    <title>Prescription Review Required</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4299E1;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-top: 20px;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #3182CE;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .approve-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }
        .reject-button {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.9em;
        }
        .document-preview {
            margin: 10px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .document-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .action-form {
            margin: 20px 0;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .action-form input {
            width: 100px;
            padding: 5px;
            margin-right: 10px;
        }
        .action-form button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Prescription Review Required</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $notifiable->name }},</p>
        
        <p>{{ $message }}</p>
        
        <!-- resources/views/emails/admin/prescription-review.blade.php -->
<!-- ... existing code ... -->
<div class="details">
    <h3>Prescription Details:</h3>
    <ul>
        <li><strong>Order ID:</strong> {{ $order_id }}</li>
        <li><strong>Drug:</strong> {{ $order->drug->name }}</li>
        <li><strong>Quantity:</strong> {{ $order->quantity }}</li>
        <li><strong>Current Refill Allowed:</strong> {{ $order->refill_allowed }}</li>
    </ul>
    
    <div class="document-preview">
        <h4>Prescription Image:</h4>
        <img src="{{ $prescriptionImageUrl }}" alt="Prescription Image" style="max-width: 100%;">
    </div>
</div>

<div class="action-form">
    <h3>Take Action:</h3>
    <form action="{{ url('/api/prescriptions/' . $order_id . '/approve') }}" method="POST">
        @csrf
        @method('PATCH')
        <button type="submit" class="approve-button">
            Approve Prescription
        </button>
    </form>

    <form action="{{ url('/api/prescriptions/' . $order_id . '/reject') }}" method="POST">
        @csrf
        @method('PATCH')
        <button type="submit" class="reject-button">
            Reject Prescription
        </button>
    </form>

    <form action="{{ url('/api/prescriptions/' . $order_id . '/refill') }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('PATCH')
        <div style="display: flex; align-items: center;">
            <input type="number" name="refill_allowed" min="0" 
                   value="{{ $order->refill_allowed }}" 
                   style="width: 100px; padding: 5px; margin-right: 10px;">
            <button type="submit" style="background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">
                Update Refill
            </button>
        </div>
    </form>
</div>


<!-- ... rest of the template ... -->
</body>
</html>