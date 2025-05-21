<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $message;

    public function __construct(Order $order, string $message)
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Mail\Message)
            ->subject('Your Order Has Been Created')
            ->view('emails.admin.order-created', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'message' => $this->message,
                'orderId' => $this->order->id
            ]);
    }
}