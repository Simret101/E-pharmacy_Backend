<!-- resources/views/payment/error.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Payment Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="alert alert-danger" role="alert">
                    <h2 class="mb-3">Payment Error</h2>
                    <p class="mb-0">{{ session('error', 'An error occurred during payment. Please try again.') }}</p>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-primary mt-3">Back to Orders</a>
            </div>
        </div>
    </div>
</body>
</html>