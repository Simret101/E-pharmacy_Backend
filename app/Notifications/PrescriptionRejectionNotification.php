<?php


namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrescriptionReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // or add 'broadcast' if needed
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Prescription Review Required')
            ->line('A new prescription order has been placed and needs your review.')
            ->line("Order ID: {$this->order->id}")
            ->action('Review Order', url("/pharmacist/orders/{$this->order->id}"))
            ->line('Please log in to your dashboard to review the details.');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'A new prescription requires your review.',
        ];
    }
}
