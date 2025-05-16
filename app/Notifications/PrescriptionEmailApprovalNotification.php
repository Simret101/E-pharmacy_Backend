<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PrescriptionEmailApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $prescription;
    protected $approveUrl;
    protected $rejectUrl;

    public function __construct(Order $order, Prescription $prescription)
    {
        $this->order = $order;
        $this->prescription = $prescription;
        $this->approveUrl = url('/api/orders/' . $order->id . '/prescription/approve');
        $this->rejectUrl = url('/api/orders/' . $order->id . '/prescription/reject');
    }

    public function via($notifiable)
    {
        return [\Illuminate\Notifications\Channels\MailChannel::class];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Prescription Approval Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new prescription requires your review. Please review the details below and take appropriate action.')
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->order_uid)
            ->line('Drug: ' . $this->order->drug->name)
            ->line('Quantity: ' . $this->order->quantity)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Current Status: ' . ucfirst($this->order->prescription_status))
            ->line('Refill Allowed: ' . ($this->order->refill_allowed ?? 'Not specified'))
            ->line('')
            ->line('Prescription Image:')
            ->attachData(
                base64_decode($this->prescription->image),
                'prescription.jpg',
                [
                    'mime' => 'image/jpeg',
                ]
            )
            ->line('')
            ->line('Please review the prescription carefully before making a decision. You can modify the refill allowance when approving the prescription.')
            ->line('')
            ->action('Approve Prescription', $this->approveUrl, [
                'color' => '#4CAF50',
                'style' => 'background-color: #4CAF50; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;'
            ])
            ->line('OR')
            ->action('Reject Prescription', $this->rejectUrl, [
                'color' => '#f44336',
                'style' => 'background-color: #f44336; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;'
            ])
            ->line('')
            ->line('This notification will expire in 24 hours. If you do not take action, the prescription will remain pending.')
            ->salutation('Best regards,')
            ->line('EPharmacy Team');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'prescription_id' => $this->prescription->id,
            'message' => 'Prescription requires approval',
            'status' => $this->prescription->status,
        ];
    }
}
