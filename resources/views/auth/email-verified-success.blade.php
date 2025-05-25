<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Email Verified</h4>
                    </div>
                    <div class="card-body text-center">
                        @if(session('success'))
                            <p class="text-success">
                                {{ session('success') }}
                            </p>
                        @elseif(session('error'))
                            <p class="text-danger">
                                {{ session('error') }}
                            </p>
                        @else
                            <p class="text-success">
                                Your email has been verified successfully!
                            </p>
                        @endif
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>