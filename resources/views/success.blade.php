<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful - E-Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 0.5em 1em;
            border-radius: 0.25rem;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle success-icon"></i>
                        </div>
                        <h2 class="card-title mb-4">Payment Successful!</h2>
                        <div class="alert alert-success">
                            Your order has been confirmed and paid successfully.
                        </div>

                        <div class="text-start mt-4">
                            <h5>Order Details:</h5>
                            <table class="table">
                                <tr>
                                    <th>Order ID:</th>
                                    <td>#{{ $order->id }}</td>
                                </tr>
                                <tr>
                                    <th>Payment ID:</th>
                                    <td>{{ $payment->payment_id }}</td>
                                </tr>
                                <tr>
                                    <th>Amount Paid:</th>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td><span class="badge-success">{{ $order->status }}</span></td>
                                </tr>
                            </table>

                            <h5 class="mt-4">Items Purchased:</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $items = json_decode($order->items, true);
                                            if (!$items || !is_array($items)) {
                                                $items = [];
                                            }
                                        @endphp
                                        @foreach($items as $item)
                                        <tr>
                                            <td>{{ $item['name'] ?? 'N/A' }}</td>
                                            <td>{{ $item['quantity'] ?? 0 }}</td>
                                            <td>${{ number_format($item['price'] ?? 0, 2) }}</td>
                                            <td>${{ number_format($item['subtotal'] ?? 0, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th>${{ number_format($order->total_amount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-muted">
                                A confirmation email has been sent to your email address.
                                Please keep this information for your records.
                            </p>
                            
                            <!-- <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                Return to Home
                            </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
