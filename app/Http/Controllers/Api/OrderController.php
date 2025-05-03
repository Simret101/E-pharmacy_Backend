<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use App\Customs\Services\CloudinaryService;

class OrderController extends Controller
{
    
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
            'prescription_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        DB::beginTransaction();

        try {
            $drug = Drug::lockForUpdate()->find($request->drug_id);
            if (!$drug) {
                DB::rollBack();
                return response()->json([
                    'message' => "Drug not found"
                ], 404);
            }

            $quantity = $request->quantity;

            if ($drug->stock === 0) {
                DB::rollBack();
                return response()->json([
                    'message' => "{$drug->name} is out of stock and cannot be ordered."
                ], 400);
            }

            if ($drug->stock < $quantity) {
                DB::rollBack();
                return response()->json([
                    'message' => "Insufficient stock for {$drug->name}. Available: {$drug->stock}"
                ], 400);
            }

            // Handle prescription image upload if provided
            $prescriptionImage = null;
            if ($request->hasFile('prescription_image')) {
                $cloudinaryService = new CloudinaryService();
                $prescriptionImage = $cloudinaryService->uploadImage($request->file('prescription_image'), 'orders');
            }

            // Update drug stock
            $drug->stock -= $quantity;
            $drug->save();

            // Create inventory log
            InventoryLog::create([
                'drug_id' => $drug->id,
                'user_id' => $user->id,
                'change_type' => 'sale',
                'quantity_changed' => -$quantity,
                'reason' => "Order placed",
            ]);

            // Calculate order details
            $price = $drug->price;
            $subtotal = $price * $quantity;

            $item = [
                'drug_id' => $drug->id,
                'name' => $drug->name,
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            // Create the order
            $order = new Order();
            $order->user_id = $user->id;
            $order->items = json_encode([$item], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $order->total_amount = $subtotal;
            $order->status = 'pending';
            $order->prescription_image = $prescriptionImage;
            $order->save();

            // Load the user relationship
            $order->load('user');

            // Notify pharmacists about the new order
            $pharmacists = \App\Models\User::where('is_role', 1)->get();
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->notify(new \App\Notifications\NewOrderNotification($order));
            }

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'data' => new OrderResource($order),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Order failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    

    public function adminOrders(Request $request)
    {
        $user = Auth::user();

        if ($user->is_role !== 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized. Only admins can access this endpoint.'
            ], 403);
        }

        try {
            $query = Order::with(['user']); // Eager load user relationship

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by minimum amount
            if ($request->has('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }

            // Filter by maximum amount
            if ($request->has('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }

            // Search in order items
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('user', function($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                    })
                    ->orWhere('items', 'like', "%{$searchTerm}%");
                });
            }

            // Sort orders
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $orders = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'from' => $orders->firstItem(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'to' => $orders->lastItem(),
                    'total' => $orders->total(),
                ],
                'links' => [
                    'first' => $orders->url(1),
                    'last' => $orders->url($orders->lastPage()),
                    'prev' => $orders->previousPageUrl(),
                    'next' => $orders->nextPageUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function userOrders()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)->get();

        return OrderResource::collection($orders);
    }



    public function show($id)
    {
        $user = Auth::user();
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        
        if ($order->user_id !== $user->id && !($user->is_admin ?? false)) {
            return response()->json([
                'message' => 'Unauthorized access to this order'
            ], 403);
        }

        return new OrderResource($order);
    }
}
