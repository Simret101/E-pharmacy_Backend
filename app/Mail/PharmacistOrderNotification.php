<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Customs\Services\CloudinaryService;

class PharmacistOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $prescriptionImageUrl;

    public function __construct(Order $order)
    {
        $this->order = $order;
        
        // Get Cloudinary URL for prescription image
        $cloudinaryService = app(CloudinaryService::class);
        $this->prescriptionImageUrl = $cloudinaryService->getImageUrl($order->prescription_image);
    }

    public function build()
    {
        return $this->subject('New Order for Review')
                    ->view('emails.pharmacist-order-notification')
                    ->with([
                        'order' => $this->order,
                        'prescriptionImage' => $this->prescriptionImage
                    ]);
    }
}
