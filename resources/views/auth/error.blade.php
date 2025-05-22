<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .error {
            color: #dc3545;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error">Error</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>