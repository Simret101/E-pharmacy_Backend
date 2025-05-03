<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed - E-Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .error-icon {
            color: #dc3545;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-times-circle error-icon"></i>
                        </div>
                        <h2 class="card-title mb-4">Payment Failed</h2>
                        <div class="alert alert-danger">
                            {{ session('error') ?? 'There was an error processing your payment.' }}
                        </div>
                        <div class="mt-4">
                            <p class="text-muted">
                                Please try again or contact our support team if the problem persists.
                            </p>
                            <a href="{{ url('/orders') }}" class="btn btn-primary">
                                Return to Orders
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
