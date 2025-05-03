@component('mail::message')
# New Paid Order Received

Dear {{ $notifiable->name }},

You have received a new paid order.

**Order Details:**
- Order ID: #{{ $order->id }}
- Customer: {{ $order->user->name }}
- Payment ID: {{ $payment->payment_id }}

**Items from Your Pharmacy:**
@component('mail::table')
| Item | Quantity | Price | Subtotal |
|:-----|:---------|:------|:---------|
@foreach($pharmacistItems as $item)
| {{ $item['name'] }} | {{ $item['quantity'] }} | ${{ number_format($item['price'], 2) }} | ${{ number_format($item['subtotal'], 2) }} |
@endforeach
| | | **Total:** | **${{ number_format($totalAmount, 2) }}** |
@endcomponent

Please process this order as soon as possible. Click the button below to view the full order details:

@component('mail::button', ['url' => url('/pharmacist/orders/' . $order->id)])
View Order Details
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endcomponent 