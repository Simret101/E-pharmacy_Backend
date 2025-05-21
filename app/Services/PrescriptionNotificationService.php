<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Mail\PrescriptionReviewMail;
use Illuminate\Support\Facades\Mail;
use App\Customs\Services\CloudinaryService;

class PrescriptionNotificationService
{
    private $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function sendPrescriptionReviewNotification(User $user, Order $order, $message)
    {
        $prescriptionImageUrl = $this->cloudinaryService->getImageUrl($order->prescription_image);
        
        Mail::to($user->email)->send(
            new PrescriptionReviewMail($user, $order, $message, $prescriptionImageUrl)
        );
    }

    public function sendPrescriptionDecisionNotification(User $user, Order $order, $status, $message)
    {
        $prescriptionImageUrl = $this->cloudinaryService->getImageUrl($order->prescription_image);
        
        Mail::to($user->email)->send(
            new PrescriptionDecisionMail($user, $order, $status, $message, $prescriptionImageUrl)
        );
    }
}