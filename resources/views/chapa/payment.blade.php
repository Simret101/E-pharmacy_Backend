<!-- resources/views/chapa/payment.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Chapa Payment</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>Pay for Order #{{ $order->id }}</h2>
    <p>Amount: {{ $order->total_amount }} ETB</p>
    <form action="{{ route('chapa.pay') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ $user->email ?? '' }}" required>
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="{{ $user->first_name ?? '' }}" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="{{ $user->last_name ?? '' }}" required>
        </div>
        <button type="submit">Pay with Chapa</button>
    </form>
</body>
</html>