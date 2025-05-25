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

// public function chapaPay(Request $request)
// {
//     $order = Order::findOrFail($request->order_id);
    
//     $chapaData = [
//         'public_key' => config('services.chapa.public_key'),
//         'tx_ref' => 'negade-tx-' . uniqid(),
//         'amount' => $order->total_amount,
//         'currency' => 'ETB',
//         'email' => $request->email,
//         'first_name' => $request->first_name,
//         'last_name' => $request->last_name,
//         'title' => 'E-Pharmacy Payment',
//         'description' => 'Payment for your order at E-Pharmacy',
//         'logo' => 'https://chapa.link/asset/images/chapa_swirl.svg',
//         'callback_url' => route('chapa.callback'),
//         'return_url' => route('chapa.success', ['order_id' => $order->id]),
//         'meta' => [
//             'order_id' => $order->id
//         ]
//     ];

//     // Redirect to Chapa's hosted payment page
//     return redirect('https://api.chapa.co/v1/hosted/pay')
//         ->with($chapaData);
// }

public function chapaCallback(Request $request)
{
    // Verify webhook signature
    $signature = $request->header('Chapa-Signature') ?? $request->header('x-chapa-signature');
    $payload = json_encode($request->all());
    
    $secret = config('services.chapa.webhook_secret');
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    
    if ($signature !== $expectedSignature) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    try {
        // Log the incoming webhook data for debugging
        \Log::info('Chapa Webhook Received:', [
            'event' => $request->event,
            'status' => $request->status,
            'reference' => $request->reference,
            'order_id' => $request->meta['order_id'] ?? null,
            'customer_id' => $request->customer->id ?? null,
            'customer_email' => $request->customer->email ?? null,
            'amount' => $request->amount ?? null,
            'currency' => $request->currency ?? null,
            'payment_method' => $request->payment_method ?? null
        ]);

        // Check if this is a successful payment event
        if ($request->event === 'charge.success' && $request->status === 'success') {
            $order_id = $request->meta['order_id'] ?? null;
            
            if ($order_id) {
                $order = Order::with(['user', 'drug'])->findOrFail($order_id);

                // Update order status to paid
                $order->status = 'paid';
                $order->save();
                
                // Create payment record
                $payment_data = [
                    'payment_id' => $request->reference,
                    'payer_id' => $request->customer->id ?? null,
                    'payer_email' => $request->customer->email ?? null,
                    'amount' => $request->amount ?? null,
                    'currency' => $request->currency ?? null,
                    'payment_status' => 'completed',
                    'payment_method' => $request->payment_method ?? null,
                    'order_id' => $order->id
                ];

                // Log payment data before creation
                \Log::info('Attempting to create payment with data:', $payment_data);

                $payment = Payment::create($payment_data);

                // Log after creation
                \Log::info('Payment created successfully:', [
                    'payment_id' => $payment->id,
                    'payment_data' => $payment->toArray()
                ]);

                // Notify the patient (who placed the order)
                $order->user->notify(new OrderPaidNotification($order, $payment));

                // Notify the pharmacist (creator of the drug)
                $pharmacist = $order->drug->creator;

                if ($pharmacist) {
                    $pharmacist->notify(new PharmacistOrderPaidNotification($order, $payment));
                }

                return response()->json(['status' => 'success']);
            } else {
                \Log::error('Order not found for webhook:', [
                    'order_id' => $order_id
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }
        } else {
            \Log::info('Non-successful payment event received:', [
                'event' => $request->event,
                'status' => $request->status
            ]);
            return response()->json(['status' => 'error', 'message' => 'Payment was not successful'], 400);
        }
    } catch (\Exception $e) {
        \Log::error('Error processing webhook:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Internal server error'], 500);
    }
}

public function chapaSuccess(Request $request)
{
    $order = Order::with(['user', 'drug'])->findOrFail($request->order_id);

    // Update order status
    $order->status = 'paid';
    $order->save();

    // Create payment record
    $payment = Payment::create([
        'payment_id' => $request->reference ?? uniqid('chapa_'), // Use Chapa reference or generate a unique ID
        'payer_id' => $order->user_id, // Use the order's user ID
        'payer_email' => $order->user->email, // Use the order's user email
        'amount' => $order->total_amount,
        'currency' => 'ETB',
        'payment_status' => 'completed',
        'order_id' => $order->id
    ]);

    // Notify the patient
    $order->user->notify(new OrderPaidNotification($order, $payment));

    // Notify the pharmacist
    $pharmacist = $order->drug->creator;
    if ($pharmacist) {
        $pharmacist->notify(new PharmacistOrderPaidNotification($order, $payment));
    }

    return view('chapa.success', compact('order', 'payment'));
}
public function showPaymentForm($orderId)
{
    $order = Order::findOrFail($orderId);
    return view('chapa.payment', compact('order'));
}

public function chapaPay(Request $request)
{
    $order = Order::findOrFail($request->order_id);
    
    $chapaData = [
        'public_key' => config('services.chapa.public_key'),
        'tx_ref' => 'negade-tx-' . uniqid(),
        'amount' => $order->total_amount,
        'currency' => 'ETB',
        'email' => $request->email,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'title' => 'E-Pharmacy Payment',
        'description' => 'Payment for your order at E-Pharmacy',
        'logo' => 'https://chapa.link/asset/images/chapa_swirl.svg',
        'return_url' => route('chapa.success', ['order_id' => $order->id]),
        'meta' => [
            'order_id' => $order->id
        ]
    ];

    // Create a form with hidden inputs
    $form = '<form id="chapaForm" method="POST" action="https://api.chapa.co/v1/hosted/pay">';
    foreach ($chapaData as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $form .= '<input type="hidden" name="' . $key . '[' . $subKey . ']" value="' . htmlspecialchars($subValue) . '">';
            }
        } else {
            $form .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
        }
    }
    $form .= '</form>';
    $form .= '<script>document.getElementById("chapaForm").submit();</script>';

    return response($form)->header('Content-Type', 'text/html');
}
}