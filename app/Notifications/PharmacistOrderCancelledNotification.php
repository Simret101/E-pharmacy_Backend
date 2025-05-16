<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmacistOrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $reason;

    public function __construct(Order $order, $reason = null)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Cancelled - ' . $this->order->order_uid)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The order ' . $this->order->order_uid . ' has been cancelled.')
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Customer: ' . $this->order->user->name)
            ->line('Cancellation Reason: ' . ($this->reason ?? 'Not specified'))
            ->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('')
            ->line('Please ensure the inventory is updated accordingly.')
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'status' => 'cancelled',
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
            'customer_name' => $this->order->user->name,
            'reason' => $this->reason,
        ];
    }
}
