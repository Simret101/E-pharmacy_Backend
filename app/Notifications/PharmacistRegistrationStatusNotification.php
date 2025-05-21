<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmacistRegistrationStatusNotification extends Notification
{
    use Queueable;

    public $status;
    public $pharmacist;

    public function __construct($status, $pharmacist)
    {
        $this->status = $status;
        $this->pharmacist = $pharmacist;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->status === 'approved' 
            ? 'Your Pharmacist Registration Has Been Approved' 
            : 'Your Pharmacist Registration Status';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $this->pharmacist->name)
            ->line('Your pharmacist registration status has been updated.')
            ->line('Status: ' . ucwords($this->status))
            ->line('Thank you for using our application!');
    }
}