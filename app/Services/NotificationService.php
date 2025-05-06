<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Prescription;
use App\Notifications\OrderStatusNotification;
use App\Notifications\PrescriptionApprovalNotification;
use App\Notifications\PrescriptionRejectionNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendOrderStatusNotification(Order $order, string $message)
    {
        try {
            $user = $order->user;
            $user->notify(new OrderStatusNotification($order, $message));
            
            Log::info('Order status notification sent', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'message' => $message
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order status notification', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            return false;
        }
    }

    public function sendPrescriptionApprovalNotification(Order $order, Prescription $prescription)
    {
        try {
            // Notify user
            $user = $order->user;
            $user->notify(new PrescriptionApprovalNotification($order, $prescription, $order->refill_allowed));

            // Notify pharmacist
            $pharmacist = User::where('id', $order->drug->created_by)->first();
            if ($pharmacist) {
                $pharmacist->notify(new PrescriptionApprovalNotification($order, $prescription, $order->refill_allowed));
            }

            Log::info('Prescription approval notifications sent', [
                'order_id' => $order->id,
                'prescription_id' => $prescription->id,
                'user_id' => $user->id,
                'pharmacist_id' => $pharmacist ? $pharmacist->id : null,
                'refill_allowed' => $order->refill_allowed,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send prescription approval notifications', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'prescription_id' => $prescription->id
            ]);
            return false;
        }
    }

    public function sendPrescriptionRejectionNotification(Order $order, Prescription $prescription)
    {
        try {
            $user = $order->user;
            $user->notify(new PrescriptionRejectionNotification($order, $prescription, $order->refill_allowed));

            Log::info('Prescription rejection notification sent', [
                'order_id' => $order->id,
                'prescription_id' => $prescription->id,
                'user_id' => $user->id,
                'refill_allowed' => $order->refill_allowed,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send prescription rejection notification', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'prescription_id' => $prescription->id
            ]);
            return false;
        }
    }

    public function sendOrderNotificationToPharmacist($order, $pharmacist)
    {
        $pharmacist->notify(new OrderStatusNotification("A new order has been placed for a drug you manage. Order ID: {$order->id}"));
    }
}