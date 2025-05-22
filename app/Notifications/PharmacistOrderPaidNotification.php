<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\Payment;

class PharmacistOrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $payment;

    public function __construct(Order $order, Payment $payment)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Payment Received - Order #' . $this->order->id)
            ->markdown('emails.order-paid-pharmacist', [
                'order' => $this->order,
                'payment' => $this->payment,
                'url' => url('/orders/' . $this->order->id)
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'payment_id' => $this->payment->payment_id,
            'amount' => $this->payment->amount,
            'type' => 'order_paid',
            'message' => 'A customer has completed payment for order #' . $this->order->id . '.'
        ];
    }
}
