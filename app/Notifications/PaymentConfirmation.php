<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class PaymentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $userType;

    public function __construct(Payment $payment, $userType)
    {
        $this->payment = $payment;
        $this->userType = $userType; // 'pharmacist' or 'patient'
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Payment Confirmation')
            ->line('Payment has been processed successfully.');

        if ($this->userType === 'pharmacist') {
            $message->line('You have received a payment of $' . number_format($this->payment->amount, 2))
                   ->line('From: ' . $this->payment->patient->name)
                   ->line('Order ID: ' . $this->payment->order_id);
                   
        } else {
            $message->line('Your payment of $' . number_format($this->payment->amount, 2) . ' has been processed')
                   ->line('To: ' . $this->payment->pharmacist->name)
                   ->line('Order ID: ' . $this->payment->order_id);
        }

        return $message->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'order_id' => $this->payment->order_id,
            'user_type' => $this->userType,
            'message' => $this->userType === 'pharmacist' 
                ? 'You have received a payment of $' . number_format($this->payment->amount, 2)
                : 'Your payment of $' . number_format($this->payment->amount, 2) . ' has been processed',
            'type' => 'payment_confirmation'
        ];
    }
} 