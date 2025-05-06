<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrescriptionRejectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $prescription;

    public function __construct(Order $order, Prescription $prescription)
    {
        $this->order = $order;
        $this->prescription = $prescription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Prescription Rejected')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We regret to inform you that your prescription has been rejected by the pharmacist.')
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Total Amount: $' . $this->order->total_amount)
            ->line('Reason for Rejection:')
            ->line('The prescription could not be verified or did not meet our requirements.')
            ->line('What to do next:')
            ->line('1. Please ensure your prescription is valid and clearly visible')
            ->line('2. Make sure the prescription matches the ordered medication')
            ->line('3. You can submit a new prescription for the same order')
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('If you have any questions, please contact our support team.');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'prescription_id' => $this->prescription->id,
            'prescription_uid' => $this->prescription->prescription_uid,
            'status' => $this->prescription->status,
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
            'message' => 'Your prescription has been rejected. Please check your email for details.',
        ];
    }
} 