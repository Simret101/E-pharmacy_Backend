<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Prescription Order Requires Review')
            ->line('A new prescription order has been submitted and requires your review.')
            ->line('Order ID: ' . $this->order->id)
            ->action('Review Order', url("/pharmacist/orders/{$this->order->id}"))
            ->line('Please log in to your dashboard to review the prescription and take appropriate action.');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'A new prescription order requires your review.',
            'action_url' => url("/pharmacist/orders/{$this->order->id}")
        ];
    }
}