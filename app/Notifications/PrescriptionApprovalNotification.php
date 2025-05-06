<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrescriptionApprovalNotification extends Notification implements ShouldQueue
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
        $message = new MailMessage;
        
        if ($notifiable->is_role === 2) { // Pharmacist
            $message->subject('New Prescription to Review')
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('A new prescription has been submitted for your review.')
                ->line('Order Details:')
                ->line('Order ID: ' . $this->order->order_uid)
                ->line('Drug: ' . $this->order->drug->name)
                ->line('Quantity: ' . $this->order->quantity)
                ->line('Prescription Status: ' . ucfirst($this->prescription->status))
                ->action('Review Prescription', url('/pharmacist/prescriptions/' . $this->prescription->id))
                ->line('Please review this prescription as soon as possible.');
        } else { // User
            $message->subject('Prescription Approved')
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Your prescription has been approved by the pharmacist.')
                ->line('Order Details:')
                ->line('Order ID: ' . $this->order->order_uid)
                ->line('Drug: ' . $this->order->drug->name)
                ->line('Quantity: ' . $this->order->quantity)
                ->line('Total Amount: $' . $this->order->total_amount)
                ->line('Refills Allowed: ' . $this->prescription->refill_allowed)
                ->action('View Order', url('/orders/' . $this->order->id))
                ->line('Your order will be processed shortly.');
        }

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_uid' => $this->order->order_uid,
            'prescription_id' => $this->prescription->id,
            'prescription_uid' => $this->prescription->prescription_uid,
            'status' => $this->prescription->status,
            'refill_allowed' => $this->prescription->refill_allowed,
            'drug_name' => $this->order->drug->name,
            'quantity' => $this->order->quantity,
            'total_amount' => $this->order->total_amount,
        ];
    }
} 