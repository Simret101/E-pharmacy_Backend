<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string'
        ]);

        try {
            // Process payment logic here
            $payment = Payment::create([
                'amount' => $request->amount,
                'order_id' => $request->order_id,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'patient_id' => Auth::id()
            ]);

            // Get the pharmacist associated with the order
            $pharmacist = $payment->order->pharmacist;

            // Send notification to pharmacist
            $pharmacist->notify(new PaymentConfirmation($payment, 'pharmacist'));

            // Send notification to patient
            Auth::user()->notify(new PaymentConfirmation($payment, 'patient'));

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 