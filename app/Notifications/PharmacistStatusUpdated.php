<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PharmacistStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $reason;
    protected $loginUrl;

    public function __construct($status, $reason = null)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->loginUrl = 'https://e-pharacy.vercel.app/login';
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => $this->getStatusMessage(),
            'loginUrl' => $this->loginUrl
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->getStatusMessage())
            ->when($this->status === 'approved', function ($message) {
                $message->action('Login to Your Account', $this->loginUrl);
            })
            ->line('Thank you for using our service!');
    }

    private function getSubject(): string
    {
        return match($this->status) {
            'approved' => 'Pharmacist Registration Approved',
            'rejected' => 'Pharmacist Registration Rejected',
            'pending' => 'Pharmacist Registration Pending',
            default => 'Pharmacist Registration Status Update'
        };
    }

    private function getStatusMessage(): string
    {
        return match($this->status) {
            'approved' => 'Your pharmacist registration has been approved. You can now log in to your account.',
            'rejected' => 'Your pharmacist registration has been rejected. Reason: ' . ($this->reason ?? 'Documents not verified'),
            'pending' => 'Your pharmacist registration is pending review.',
            default => 'Your pharmacist registration status has been updated.'
        };
    }
}