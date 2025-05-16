<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Confirmed for Order #'. $this->order->order_uid)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment for order #'. $this->order->order_uid . ' has been successfully confirmed.')
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Total Amount: $' . $this->order->total_amount)
            ->line('Status: Paid')
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Your order will be processed shortly. Thank you for your payment!');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'message' => 'Payment confirmed for your order',
            'status' => 'paid',
        ];
    }
}
