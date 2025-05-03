<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmacistVerificationStatus extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $reason;
    protected $pharmacist;

    public function __construct($status, $reason = null, $pharmacist)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->pharmacist = $pharmacist;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = url('/api/admin/pharmacists/' . $this->pharmacist->id . '/status');
        
        return (new MailMessage)
            ->subject('New Pharmacist Registration - TIN Verification Required')
            ->markdown('emails.pharmacist-verification', [
                'pharmacist' => $this->pharmacist,
                'url' => $url
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => $this->status === 'approved' 
                ? 'Your pharmacist account has been approved' 
                : 'Your pharmacist account verification was rejected',
            'type' => 'verification_status'
        ];
    }
} 