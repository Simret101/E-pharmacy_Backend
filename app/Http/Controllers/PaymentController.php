<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Drug;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Mail\PaymentNotification;
use App\Notifications\OrderPaidNotification;
use App\Notifications\PharmacistOrderPaidNotification;
use App\Notifications\NewOrderNotification;
use App\Services\PaymentNotificationService;

class PaymentController extends Controller
{
    private $gateway;
    private $paymentNotificationService;

    public function __construct(PaymentNotificationService $paymentNotificationService)
    {
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(config('services.paypal.client_id'));
        $this->gateway->setSecret(config('services.paypal.secret'));
        $this->gateway->setTestMode(true);

        $this->paymentNotificationService = $paymentNotificationService;
    }

    public function pay(Request $request)
{
    // Validate the order ID only
    $request->validate([
        'order_id' => 'required|exists:orders,id',
    ]);

    // Retrieve the order using the ID
    $order = Order::findOrFail($request->order_id);

    try {
        // Use the amount from the database (trusted source)
        $response = $this->gateway->purchase([
            'amount' => number_format($order->total_amount, 2, '.', ''), // Ensure proper format
            'currency' => config('services.paypal.currency'),
            'returnUrl' => route('payment.success', ['order_id' => $order->id]),
            'cancelUrl' => route('payment.error'),
        ])->send();

        if ($response->isRedirect()) {
            return $response->redirect();
        }

        return $response->getMessage();
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
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
                $order = Order::with(['user', 'drug'])->findOrFail($orderId);

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
                // Notify the patient (who placed the order)
                $order->user->notify(new OrderPaidNotification($order, $payment));

                // Notify the pharmacist (creator of the drug)
                $pharmacist = $order->drug->creator;

                if ($pharmacist) {
                    $pharmacist->notify(new PharmacistOrderPaidNotification($order, $payment));
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
}