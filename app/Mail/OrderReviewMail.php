<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Prescription;
use App\Customs\Services\CloudinaryService;

class OrderReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $prescriptionImageUrl;
    public $prescriptionId;

    public function __construct(Order $order)
    {
        $this->order = $order;
        
        // Get prescription image URL using Cloudinary service
        $cloudinaryService = app(CloudinaryService::class);
        $this->prescriptionImageUrl = $order->prescription ? $cloudinaryService->getImageUrl($order->prescription->image_url) : null;
        $this->prescriptionId = $order->prescription ? $order->prescription->id : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order Requires Review - Order #' . $this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.order-review',
            with: [
                'order' => $this->order,
                'prescriptionImageUrl' => $this->prescriptionImageUrl,
                'prescriptionId' => $this->prescriptionId,
            ],
        );
    }
}
