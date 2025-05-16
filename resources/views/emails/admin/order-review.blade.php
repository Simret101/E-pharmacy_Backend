@extends('emails.layouts.admin')

@section('content')
    <h2>New Order Requires Review</h2>

    <p>A new order has been placed and requires your review:</p>

    <div class="order-details">
        <h3>Order Details</h3>
        <ul>
            <li><strong>Order ID:</strong> {{ $order->id }}</li>
            <li><strong>Drug:</strong> {{ $order->drug->name }}</li>
            <li><strong>Quantity:</strong> {{ $order->quantity }}</li>
            <li><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</li>
            <li><strong>Status:</strong> Pending</li>
        </ul>
    </div>

    @if($prescriptionImageUrl)
        <div class="prescription-section">
            <h3>Prescription Details</h3>
            <p>Prescription ID: {{ $prescriptionId }}</p>
            
            <div class="prescription-image">
                <img src="{{ $prescriptionImageUrl }}" alt="Prescription Image" style="max-width: 100%; height: auto;">
            </div>
        </div>
    @endif

    <div class="action-buttons">
        <p>Take action on this order:</p>
        <div class="buttons-container">
            <a href="{{ route('orders.approve', ['order' => $order->id]) }}" class="approve-button">
                <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                    Approve Order
                </button>
            </a>
            <a href="{{ route('orders.reject', ['order' => $order->id]) }}" class="reject-button">
                <button style="background-color: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                    Reject Order
                </button>
            </a>
        </div>
    </div>

    <p>Please review the prescription and take appropriate action.</p>
@endsection
