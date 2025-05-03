<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewOrderNotification extends Notification implements ShouldQueue
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
        $orderUrl = url('/admin/orders/' . $this->order->id);
        
        return (new MailMessage)
            ->subject('New Order Received')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new order has been placed in the system.')
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->id)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Number of Items: ' . count(json_decode($this->order->items)))
            ->action('View Order', $orderUrl)
            ->line('Please review the order and process it accordingly.');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total_amount' => $this->order->total_amount,
            'prescription_uid' => $this->order->prescription_uid,
            'prescription_image' => $this->order->prescription_image,
        ];
    }
} 