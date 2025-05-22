<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PharmacistPaymentReceivedNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $payment;

    public function __construct($order, $payment)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Payment Received for Order #' . $this->order->id)
                    ->line('A payment has been received for Order #' . $this->order->id . '.')
                    ->line('Drug: ' . $this->order->drug->name)
                    ->line('Quantity: ' . $this->order->quantity)
                    ->line('Total Amount: ' . $this->order->total_amount)
                    ->line('Payment ID: ' . $this->payment->payment_id)
                    ->action('View Order', url('/pharmacist/orders/' . $this->order->id))
                    ->line('Thank you for your attention.');
    }
}