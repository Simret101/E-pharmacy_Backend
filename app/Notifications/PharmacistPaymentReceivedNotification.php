<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmacistPaymentReceivedNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Received - ' . $this->order->order_uid)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A payment has been received for order ' . $this->order->order_uid)
            ->line('Payment Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Customer: ' . $this->order->user->name)
            ->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('')
            ->line('Please prepare the order for shipping.')
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'status' => 'payment_received',
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
            'customer_name' => $this->order->user->name,
        ];
    }
}
