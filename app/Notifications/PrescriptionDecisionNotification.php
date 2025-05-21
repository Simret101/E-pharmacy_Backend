<?php
// app/Notifications/PrescriptionDecisionNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class PrescriptionDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $decision;
    protected $message;

    public function __construct(Order $order, $decision, $message)
    {
        $this->order = $order;
        $this->decision = $decision;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Prescription Status Update')
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->message)
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Prescription Status: ' . ucfirst($this->decision))
            ->line('Refill Allowed: ' . ($this->order->refill_allowed ?? 'Not specified'))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
{
    return [
        'order_id' => $this->order->id,
        'order_uid' => $this->order->order_uid,
        'drug' => [
            'id' => $this->order->drug->id,
            'name' => $this->order->drug->name,
            'price' => $this->order->drug->price
        ],
        'quantity' => $this->order->quantity,
        'total_amount' => $this->order->total_amount,
        'prescription_status' => $this->decision,
        'refill_allowed' => $this->order->refill_allowed,
        'refill_used' => $this->order->refill_used,
        'message' => $this->message,
        'created_at' => $this->order->created_at->format('Y-m-d H:i:s'),
        'view_url' => url('/orders/' . $this->order->id)
    ];
}}