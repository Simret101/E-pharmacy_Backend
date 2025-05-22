<!-- resources/views/emails/password-reset.blade.php -->
@component('mail::message')
# Password Reset Request

Dear {{ $user->name }},

We have received a request to reset your password. Please use the following token to proceed with the password reset process:

**Token:** {{ $token }}

This token will expire in 5 minutes. If you did not request a password reset, please ignore this email.

Best regards,
{{ config('app.name') }}
@endcomponent