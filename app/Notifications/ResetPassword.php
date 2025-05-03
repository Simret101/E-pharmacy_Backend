<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        try {
            Log::info('Starting password reset email for: ' . $notifiable->email);
            
            $resetUrl = url('/api/auth/password/reset/' . $this->token);
            Log::info('Reset URL generated: ' . $resetUrl);

            // Create a simple email message
            $message = new MailMessage;
            $message->subject('Reset Your Password')
                   ->greeting('Hello ' . $notifiable->name . ',')
                   ->line('You are receiving this email because we received a password reset request for your account.')
                   ->action('Reset Password', $resetUrl)
                   ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
                   ->line('If you did not request a password reset, no further action is required.')
                   ->salutation('Best regards,')
                   ->line(config('app.name'));

            Log::info('Email message prepared for: ' . $notifiable->email);
            
            // Send a test email first
            Mail::raw('Test email before password reset', function($message) use ($notifiable) {
                $message->to($notifiable->email)
                       ->subject('Test Email Before Reset');
            });
            Log::info('Test email sent successfully to: ' . $notifiable->email);

            return $message;

        } catch (\Exception $e) {
            Log::error('Password reset email error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
} 