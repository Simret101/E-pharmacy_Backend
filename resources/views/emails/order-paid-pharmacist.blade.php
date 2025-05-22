@component('mail::message')
# Order Payment Received

A customer has completed payment for the following order:

- **Order ID:** {{ $order->id }}
- **Payment ID:** {{ $payment->payment_id }}
- **Amount:** ${{ number_format($payment->amount, 2) }}

@component('mail::button', ['url' => $url])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
