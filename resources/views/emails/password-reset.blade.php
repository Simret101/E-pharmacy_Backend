<!-- resources/views/emails/password-reset.blade.php -->
<p>Hello,</p>
<p>Click the link below to reset your password:</p>
<p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
<p>This link will expire in {{ $expire }} minutes.</p>
