<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $message;

    public function __construct(Order $order, $message)
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $isPharmacist = $notifiable->hasRole('pharmacist');
        
        $subject = $isPharmacist ? 'Prescription Approval Required' : 'Order Status Update';
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->message)
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Status: ' . ucfirst($this->order->status))
            ->line('Prescription Status: ' . ucfirst($this->order->prescription_status))
            ->line('Refill Allowed: ' . ($this->order->refill_allowed ?? 'Not specified'))
            ->line('')
            ->line($isPharmacist ? 'Prescription Image:' : 'Prescription Details:')
            ->attachData(
                base64_decode($this->order->prescription_image),
                'prescription.jpg',
                [
                    'mime' => 'image/jpeg',
                ]
            )
            ->line('')
            ->line($isPharmacist ? 'Please review the prescription carefully before making a decision. You can modify the refill allowance when approving the prescription.' : 'Thank you for using our service!')
            ->line('')
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('')
            ->line($isPharmacist ? 'This notification will expire in 24 hours. If you do not take action, the prescription will remain pending.' : 'You will be notified when the prescription is reviewed.')
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'message' => $this->message,
            'status' => $this->order->status,
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
        ];
    }
} 