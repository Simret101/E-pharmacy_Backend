<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="alert alert-success" role="alert">
                    <h2 class="mb-3">Payment Processed Successfully!</h2>
                    <p class="mb-0">Thank you for your purchase. Order ID: {{ $order->id }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>