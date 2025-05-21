<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Customs\Services\CloudinaryService;

class PrescriptionReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $message;
    protected $prescriptionImageUrl;

    public function __construct(Order $order, $message)
    {
        $this->order = $order;
        $this->message = $message;
        
        // Get prescription image URL
        $cloudinaryService = app(CloudinaryService::class);
        $this->prescriptionImageUrl = $cloudinaryService->getImageUrl($order->prescription_image);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Mail\Message)
            ->subject('Prescription Review Required')
            ->view('emails.admin.prescription-review', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'message' => $this->message,
                'prescriptionImageUrl' => $this->prescriptionImageUrl
            ]);
    }
}