<!DOCTYPE html>
<html>
<head>
    <title>Status Update - {{ ucfirst($status) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .success-icon {
            color: {{ $status === 'approved' ? '#4CAF50' : '#f44336' }};
            font-size: 48px;
            margin-bottom: 20px;
        }
        .message {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            background-color: {{ $status === 'approved' ? '#E8F5E9' : '#FFEBEE' }};
            color: {{ $status === 'approved' ? '#2E7D32' : '#C62828' }};
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            {!! $status === 'approved' ? '✓' : '×' !!}
        </div>
        <div class="message">
            {{ $message }}
        </div>
        <div class="status">
            Status: {{ ucfirst($status) }}
        </div>
    </div>
</body>
</html> 