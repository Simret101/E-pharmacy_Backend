@component('mail::message')
# Payment Confirmation

Dear {{ $order->user->name }},

Thank you for your payment. Your order has been confirmed.

**Order Details:**
- Order ID: #{{ $order->id }}
- Payment ID: {{ $payment->payment_id }}
- Total Amount: ${{ number_format($payment->amount, 2) }}

**Items Purchased:**
@component('mail::table')
| Item | Quantity | Price | Subtotal |
|:-----|:---------|:------|:---------|
@foreach(json_decode($order->items, true) as $item)
| {{ $item['name'] }} | {{ $item['quantity'] }} | ${{ number_format($item['price'], 2) }} | ${{ number_format($item['subtotal'], 2) }} |
@endforeach
| | | **Total:** | **${{ number_format($order->total_amount, 2) }}** |
@endcomponent

You can track your order status by clicking the button below:

@component('mail::button', ['url' => url('/orders/' . $order->id)])
View Order Details
@endcomponent

Thank you for shopping with us!

Best regards,<br>
{{ config('app.name') }}
@endcomponent 