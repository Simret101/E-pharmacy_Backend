<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Prescription;
use App\Notifications\OrderStatusNotification;
use App\Notifications\PrescriptionApprovalNotification;
use App\Notifications\PrescriptionRejectionNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Notifications\PrescriptionEmailApprovalNotification;
use Illuminate\Support\Facades\Log;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\PrescriptionReviewNotification;
use App\Notifications\PrescriptionDecisionNotification;
use App\Notifications\PharmacistRegistrationStatusNotification;
use App\Notifications\PharmacistStatusNotification;
class NotificationService
{
    protected function notifyUser(User $user, $notification, array $context = []): bool
    {
        try {
            $user->notify($notification);
            Log::channel('notifications')->info('Notification sent', array_merge([
                'user_id' => $user->id,
                'notification' => class_basename($notification)
            ], $context));
            return true;
        } catch (\Exception $e) {
            Log::channel('notifications')->error('Failed to send notification', array_merge([
                'user_id' => $user->id ?? null,
                'notification' => class_basename($notification),
                'error' => $e->getMessage()
            ], $context));
            return false;
        }
    }

    public function sendOrderStatusNotification(Order $order, string $message): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        return $this->notifyUser($order->user, new OrderStatusNotification($order, $message), [
            'order_id' => $order->id,
            'message' => $message
        ]);
    }

  
    public function sendPaymentConfirmationNotification(Order $order): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        // Notify the user
        $successUser = $this->notifyUser(
            $order->user,
            new PaymentConfirmationNotification($order),
            [
                'order_id' => $order->id
            ]
        );

        // Notify the pharmacist
        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'Payment has been received for your order.'),
                    [
                        'order_id' => $order->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    /**
     * Send notification to pharmacist about their registration status
     * @param User $pharmacist
     * @param string $status
     * @return bool
     */
    // public function sendPharmacistRegistrationStatusNotification(User $pharmacist, string $status): bool
    // {
    //     if ($status === 'pending') {
    //         $message = 'Your pharmacist registration is pending approval.';
    //     } elseif ($status === 'rejected') {
    //         $message = 'Your pharmacist registration has been declined.';
    //     } else {
    //         return false;
    //     }

    //     return $this->notifyUser($pharmacist, new PharmacistStatusNotification($pharmacist, $status), [
    //         'user_id' => $pharmacist->id,
    //         'status' => $status,
    //         'message' => $message
    //     ]);
    // }
    public function sendPharmacistRegistrationStatusNotification($user, $status)
    {
        $user->notify(new PharmacistRegistrationStatusNotification($status, $user));
        // Send notification to the user
        
    }

    public function sendPrescriptionApprovalNotification(Order $order, Prescription $prescription, $refillAllowed = null): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        // Notify the user
        $successUser = $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, 'Your prescription has been approved. You can now proceed with payment.'),
            [
                'order_id' => $order->id,
                'prescription_id' => $prescription->id,
                'refill_allowed' => $refillAllowed ?? $order->refill_allowed
            ]
        );

        // Notify the pharmacist
        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'A prescription has been approved. Payment is pending.'),
                    [
                        'order_id' => $order->id,
                        'prescription_id' => $prescription->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    public function sendPrescriptionRejectionNotification(Order $order, Prescription $prescription, $reason = null): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        // Notify the user
        $successUser = $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, 'Your prescription has been rejected. Reason: ' . ($reason ?? 'Not specified')),
            [
                'order_id' => $order->id,
                'prescription_id' => $prescription->id
            ]
        );

        // Notify the pharmacist
        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'A prescription has been rejected. Reason: ' . ($reason ?? 'Not specified')),
                    [
                        'order_id' => $order->id,
                        'prescription_id' => $prescription->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    public function sendOrderShippedNotification(Order $order): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        $successUser = $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, 'Your order has been shipped. You will receive it soon.'),
            [
                'order_id' => $order->id
            ]
        );

        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'Your order has been shipped.'),
                    [
                        'order_id' => $order->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    public function sendOrderDeliveredNotification(Order $order): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        $successUser = $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, 'Your order has been delivered successfully. Thank you for shopping with us!'),
            [
                'order_id' => $order->id
            ]
        );

        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'Your order has been successfully delivered.'),
                    [
                        'order_id' => $order->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    public function sendOrderCancelledNotification(Order $order, $reason = null): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        $message = 'Your order has been cancelled. Reason: ' . ($reason ?? 'Not specified');
        $successUser = $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, $message),
            [
                'order_id' => $order->id
            ]
        );

        $pharmacistId = $order->drug->created_by ?? null;
        $successPharmacist = false;

        if ($pharmacistId) {
            $pharmacist = User::find($pharmacistId);
            if ($pharmacist) {
                $successPharmacist = $this->notifyUser(
                    $pharmacist,
                    new OrderStatusNotification($order, 'An order has been cancelled. Reason: ' . ($reason ?? 'Not specified')),
                    [
                        'order_id' => $order->id,
                        'pharmacist_id' => $pharmacist->id
                    ]
                );
            }
        }

        return $successUser && $successPharmacist;
    }

    public function sendRefillReminderNotification(Order $order): bool
    {
        if (!$order->user) {
            Log::warning('Order has no associated user', ['order_id' => $order->id]);
            return false;
        }

        return $this->notifyUser(
            $order->user,
            new OrderStatusNotification($order, 'Your prescription is eligible for refill. Would you like to place a new order?'),
            [
                'order_id' => $order->id
            ]
        );
    }

    public function sendLowStockNotification(Drug $drug): bool
    {
        $pharmacistId = $drug->created_by ?? null;
        if (!$pharmacistId) {
            Log::warning('No pharmacist associated with drug', ['drug_id' => $drug->id]);
            return false;
        }

        $pharmacist = User::find($pharmacistId);
        if (!$pharmacist) {
            Log::warning('Pharmacist not found', ['pharmacist_id' => $pharmacistId]);
            return false;
        }

        return $this->notifyUser(
            $pharmacist,
            new OrderStatusNotification($drug, 'Low stock alert: ' . $drug->name . ' has low stock. Please restock soon.'),
            [
                'drug_id' => $drug->id,
                'pharmacist_id' => $pharmacist->id
            ]
        );
    }
    public function sendOrderCreatedNotification($user, $order, $message)
    {
        try {
            $user->notify(new OrderCreatedNotification($order, $message));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send order created notification: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPrescriptionReviewNotification($pharmacist, $order, $prescription, $message)
    {
        try {
            $pharmacist->notify(new PrescriptionReviewNotification($order, $prescription, $message));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send prescription review notification: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPrescriptionDecisionNotification($user, $order, $status, $message)
    {
        try {
            $user->notify(new PrescriptionDecisionNotification($order, $status, $message));
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send prescription decision notification: ' . $e->getMessage());
            return false;
        }
    }
}
