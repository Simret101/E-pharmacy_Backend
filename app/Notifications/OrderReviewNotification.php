<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\VonageChannel;
use Illuminate\Notifications\Channels\SlackChannel;
use Illuminate\Notifications\Channels\NexmoChannel;
use Illuminate\Notifications\Channels\TelegramChannel;
use Illuminate\Notifications\Channels\VoiceChannel;
use Illuminate\Notifications\Channels\PusherChannel;
use Illuminate\Notifications\Channels\TwilioChannel;
use Illuminate\Notifications\Channels\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderReviewNotification extends OrderStatusNotification
{
    use Queueable, SerializesModels;

    protected $prescription;

    public function __construct(Order $order, string $message, Prescription $prescription = null)
    {
        parent::__construct($order, $message);
        $this->prescription = $prescription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('New Order Requires Review')
            ->greeting('Hello!')
            ->line($this->message)
            ->line('Order Details:')
            ->line("Order ID: {$this->order->id}")
            ->line("Drug: {$this->order->drug->name}")
            ->line("Quantity: {$this->order->quantity}")
            ->line("Total Amount: $" . number_format($this->order->total_amount, 2))
            ->line('Status: Pending');

        if ($this->prescription && $this->prescription->image_url) {
            // If using Cloudinary or similar service
            $mailMessage->line('Prescription Image:')
                        ->line($this->prescription->image_url)
                        ->action('View Prescription', $this->prescription->image_url);
        }

        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => $this->message,
            'prescription' => $this->prescription ? [
                'id' => $this->prescription->id,
                'image_url' => $this->prescription->image_url,
                'created_at' => $this->prescription->created_at->format('Y-m-d H:i:s')
            ] : null,
            'order_details' => [
                'drug' => $this->order->drug->name,
                'quantity' => $this->order->quantity,
                'total_amount' => $this->order->total_amount
            ]
        ];
    }
}