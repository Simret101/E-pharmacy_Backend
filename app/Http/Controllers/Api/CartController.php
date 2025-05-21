<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Drug;
use App\Models\InventoryLog;
use App\Notifications\OrderStatusNotification;
use App\Notifications\PrescriptionApprovalNotification;
use App\Notifications\PrescriptionRejectionNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Customs\Services\CloudinaryService;
use App\Services\OcrService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
   

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Cart::with('drug')->where('user_id', Auth::id());

            // Apply search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('drug', function($drugQuery) use ($searchTerm) {
                        $drugQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('description', 'like', "%{$searchTerm}%")
                                ->orWhere('brand', 'like', "%{$searchTerm}%");
                    });
                });
            }

            // Apply filters
            if ($request->has('drug_id')) {
                $query->where('drug_id', $request->drug_id);
            }

            // Apply sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $cartItems = $query->paginate($perPage)->withQueryString();

            return response()->json([
                'status' => 'success',
                'message' => 'Cart items retrieved successfully',
                'data' => $cartItems,
                'meta' => [
                    'current_page' => $cartItems->currentPage(),
                    'from' => $cartItems->firstItem(),
                    'last_page' => $cartItems->lastPage(),
                    'per_page' => $cartItems->perPage(),
                    'to' => $cartItems->lastItem(),
                    'total' => $cartItems->total(),
                    'total_quantity' => $cartItems->sum('quantity'),
                    'total_amount' => $cartItems->sum(function($item) {
                        return $item->quantity * $item->drug->price;
                    })
                ],
                
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching cart items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'drug_id' => 'required|exists:drugs,id',
                'quantity' => 'required|integer|min:1'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            DB::beginTransaction();
    
            $cart = Cart::updateOrCreate(
                ['user_id' => Auth::id(), 'drug_id' => $request->drug_id],
                ['quantity' => $request->quantity]
            );
    
            DB::commit();
    
            return response()->json([
                'message' => 'Item added to cart successfully',
                'data' => $cart->load('drug')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error adding item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $cart = Cart::with('drug')->find($id);

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart item not found'
                ], 404);
            }

            return response()->json($cart);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cart = Cart::find($id);

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart item not found'
                ], 404);
            }

            DB::beginTransaction();

            $cart->update([
                'quantity' => $request->quantity
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cart item updated successfully',
                'data' => $cart->load('drug')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();
            
           

            $cart = $user->carts()->where('id', $id)->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart item not found or does not belong to you'
                ], 404);
            }

            DB::beginTransaction();

            // Delete the cart item
            $cart->delete();

            DB::commit();

            return response()->json([
                'message' => 'Cart item removed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error removing cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Checkout cart items and create orders
     */
    public function checkout(Request $request)
{
    try {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'prescription_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cart_id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        // Upload the prescription image
        $cloudinaryService = app()->make(CloudinaryService::class);
        $imageResult = $cloudinaryService->uploadImage($request->file('prescription_image'), 'prescriptions');

        // Generate hash from image content
        $prescriptionImage = $request->file('prescription_image');
        $imageContent = file_get_contents($prescriptionImage->getRealPath());
        $imageHash = hash('sha256', $imageContent);

        // Log the generated prescription UID
        \Log::info('Generated prescription UID:', ['prescription_uid' => $imageHash]);

        // Check for duplicate prescription or refill eligibility
        $existingOrder = Order::where('prescription_uid', $imageHash)
            ->where('user_id', $user->id)
            ->first();

        if ($existingOrder) {
            if ($existingOrder->refill_allowed <= 0) {
                return response()->json([
                    'message' => 'This prescription image has already been submitted and cannot be reused.',
                    'existing_order_id' => $existingOrder->id
                ], 409);
            }

            // Decrement the refill count
            $existingOrder->refill_allowed -= 1;
            $existingOrder->save();

            // Clear the cart item
            $cart = Cart::where('id', $request->cart_id)
                ->where('user_id', $user->id)
                ->with('drug')
                ->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart item not found',
                    'error' => 'cart_item_not_found'
                ], 404);
            }

            $cart->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully using existing prescription',
                'data' => $existingOrder
            ], 201);
        }

        // Get cart item and check stock
        $cart = Cart::where('id', $request->cart_id)
            ->where('user_id', $user->id)
            ->with('drug')
            ->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart item not found',
                'error' => 'cart_item_not_found'
            ], 404);
        }

        if ($cart->drug->stock < $cart->quantity) {
            return response()->json([
                'message' => "Insufficient stock for drug: {$cart->drug->name}",
                'error' => 'insufficient_stock'
            ], 400);
        }

        // Calculate total amount
        $totalAmount = $cart->drug->price * $cart->quantity;

        // Create the order with prescription data
        $order = Order::create([
            'user_id' => $user->id,
            'drug_id' => $cart->drug_id,
            'prescription_uid' => $imageHash,
            'prescription_image' => $imageResult['secure_url'],
            'quantity' => $cart->quantity,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'prescription_status' => 'pending', // Add this field to track prescription status
            'refill_allowed' => 0
        ]);

        // Create inventory log
        InventoryLog::create([
            'drug_id' => $cart->drug_id,
            'user_id' => Auth::id(),
            'change_type' => 'sale',
            'quantity_changed' => -$cart->quantity,
            'reason' => 'Sale made through order',
            'order_id' => $order->id
        ]);

        // Reduce stock
        $cart->drug->stock -= $cart->quantity;
        $cart->drug->save();

        // Send notifications
        $this->notificationService->sendOrderCreatedNotification($user, $order, 'Your order has been created successfully. We will process it shortly.');

        // Notify pharmacist about new order
        $pharmacist = User::find($cart->drug->created_by);
        if ($pharmacist) {
            $this->notificationService->sendPrescriptionReviewNotification($pharmacist, $order, $order, 'A new prescription requires your review. Please check the details.');
        }

        // Clear the cart item
        $cart->delete();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Checkout failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'cart_id' => $request->cart_id
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create order',
            'error' => $e->getMessage()
        ], 500);
    }
}}