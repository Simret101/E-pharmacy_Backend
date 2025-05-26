<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OtpResetPassword extends Notification
{
    use Queueable;

    protected $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Password Reset OTP')
            ->line('You have requested to reset your password.')
            ->line('Your One-Time Password (OTP) is: **' . $this->otp . '**')
            ->line('This OTP is valid for ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('If you did not request a password reset, please ignore this email.');
    }
}