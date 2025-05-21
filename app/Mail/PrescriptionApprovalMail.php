<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class PrescriptionApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $refillAllowed;

    public function __construct(Order $order, bool $refillAllowed)
    {
        $this->order = $order;
        $this->refillAllowed = $refillAllowed;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Prescription Order Approved')
            ->line('Your prescription order has been approved.')
            ->line('Order ID: ' . $this->order->id)
            ->line('Prescription ID: ' . $this->order->prescription_uid)
            ->line($this->refillAllowed ? 'Refill is allowed for this prescription.' : 'Refill is not allowed for this prescription.')
            ->action('View Order', url('/orders/' . $this->order->id));
    }
}