<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefillReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Prescription Refill Reminder')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your prescription is eligible for refill.')
            ->line('Prescription Details:')
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Last Order ID: ' . $this->order->order_uid)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Refills Remaining: ' . ($this->order->refill_allowed ?? 'Not specified'))
            ->action('Place New Order', url('/orders/new'))
            ->line('Please let us know if you would like to place a new order for this prescription.')
            ->line('')
            ->line('If you no longer need this medication, please let us know so we can update your records.')
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'refill_allowed' => $this->order->refill_allowed,
        ];
    }
}
