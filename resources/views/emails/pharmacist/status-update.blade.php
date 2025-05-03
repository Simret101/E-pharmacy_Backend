@component('mail::message')
# Registration Status Update

Hello {{ $user->name }},

Your registration has been {{ $status }}.

@if($reason)
**Reason:** {{ $reason }}
@endif

@if($status === 'approved')
You can now login to your account and start using our services.

@component('mail::button', ['url' => config('app.url') . '/login'])
Login to Your Account
@endcomponent

Thank you for registering with us!
@else
If you have any questions or need further assistance, please contact our support team.
@endif

Best regards,<br>
The EPharmacy Team
@endcomponent 