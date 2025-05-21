<?php

namespace App\Services;

use App\Notifications\PaymentReceivedNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Notification;

class PaymentNotificationService
{
    public function sendPaymentNotifications(Order $order)
    {
        // Notify the patient
        Notification::send($order->user, new PaymentReceivedNotification($order));

        // Notify the pharmacist who created the drug
        $pharmacist = $order->drug->createdBy;
        if ($pharmacist) {
            Notification::send($pharmacist, new PaymentReceivedNotification($order));
        }
    }
}