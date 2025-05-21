<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmacistStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $status;
    protected $message;

    public function __construct($user, $status, $message)
    {
        $this->user = $user;
        $this->status = $status;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pharmacist Registration Status Update')
            ->greeting('Hello ' . $this->user->name)
            ->line($this->message)
            ->line('Your registration status is now: ' . ucfirst($this->status))
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'user_id' => $this->user->id,
            'created_at' => now()
        ];
    }
}