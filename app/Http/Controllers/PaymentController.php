<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderPaidNotification;
use App\Notifications\NewOrderNotification;

class PaymentController extends Controller
{
    private $gateway;

    public function __construct(){
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET'));
        $this->gateway->setTestMode(true);
    }

    public function pay(Request $request)
{
    try {
       
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        $response = $this->gateway->purchase([
            'amount' => $order->total_amount,
            'currency' => env("PAYPAL_CURRENCY"),
            'returnUrl' => url('success?order_id=' . $order->id),
            'cancelUrl' => url('error'),
        ])->send();

        if ($response->isRedirect()) {
            return $response->redirect();
        } else {
            return $response->getMessage();
        }

    } catch (\Throwable $th) {
        return $th->getMessage();
    }
}

public function success(Request $request)
{
    if ($request->input('paymentId') && $request->input('PayerID')) {
        try {
            $transaction = $this->gateway->completePurchase([
                'payer_id' => $request->input('PayerID'),
                'transactionReference' => $request->input('paymentId')
            ]);

            $response = $transaction->send();

            if ($response->isSuccessful()) {
                $data = $response->getData();
                
                $orderId = $request->input('order_id');
                $order = Order::with(['user'])->findOrFail($orderId);

                // Update order status
                $order->status = 'paid';
                $order->save();
                
                // Create payment record
                $payment = Payment::create([
                    'payment_id' => $data['id'],
                    'payer_id' => $data['payer']['payer_info']['payer_id'],
                    'payer_email' => $data['payer']['payer_info']['email'],
                    'amount' => $order->total_amount,
                    'currency' => $data['transactions'][0]['amount']['currency'],
                    'payment_status' => $data['state'],
                    'order_id' => $order->id,
                ]);

                // Get order items to find pharmacist
                $items = json_decode($order->items, true);
                $drugIds = array_column($items, 'drug_id');
                
                // Get unique pharmacists for the drugs in the order
                $pharmacists = User::whereIn('id', function($query) use ($drugIds) {
                    $query->select('created_by')
                          ->from('drugs')
                          ->whereIn('id', $drugIds);
                })->get();

                // Send email to patient
                $order->user->notify(new OrderPaidNotification($order, $payment));

                // Send email to each pharmacist
                foreach ($pharmacists as $pharmacist) {
                    $pharmacist->notify(new NewOrderNotification($order, $payment));
                }

                return view('success', [
                    'order' => $order,
                    'payment' => $payment
                ]);
            } else {
                return redirect()->route('payment.error')->with('error', $response->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            return redirect()->route('payment.error')->with('error', 'An error occurred while processing the payment.');
        }
    } else {
        return redirect()->route('payment.error')->with('error', 'Payment was declined or cancelled.');
    }
}
    
        public function error(){
        return "User declined the payment";
    }
}
