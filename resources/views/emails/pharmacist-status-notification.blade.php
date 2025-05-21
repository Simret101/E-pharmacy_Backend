@component('mail::message')
# {{ $statusMessage }}

Dear {{ $user->name }},

Your pharmacist registration status has been updated.

**Status:** {{ ucfirst($status) }}

@if($status === 'approved')
You can now log in to your account using the following link:

@component('mail::button', ['url' => $loginUrl])
Login to Your Account
@endcomponent
@endif

If you have any questions, please contact our support team.

Best regards,
{{ config('app.name') }} Team
@endcomponent
