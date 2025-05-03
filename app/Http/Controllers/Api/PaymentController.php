<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|in:card,paypal,bank_transfer'
        ]);

        try {
            DB::beginTransaction();

            // Get the order with its drug
            $order = Order::with('drug')->findOrFail($request->order_id);

            // Verify the order belongs to the authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized access to this order'
                ], 403);
            }

            // Create payment record
            $payment = Payment::create([
                'amount' => $order->total_amount,
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed',
                'payer_id' => Auth::id(),
                'payer_email' => Auth::user()->email,
                'currency' => 'USD' // or your default currency
            ]);

            // Update order status
            $order->status = 'paid';
            $order->save();

            // Get pharmacists to notify
            $pharmacists = User::where('is_role', 1)->get();

            // Send notifications
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->notify(new PaymentConfirmation($payment, 'pharmacist'));
            }
            
            Auth::user()->notify(new PaymentConfirmation($payment, 'patient'));

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 