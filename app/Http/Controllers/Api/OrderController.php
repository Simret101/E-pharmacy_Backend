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
use App\Models\OrderItem;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use App\Notifications\PrescriptionApprovalNotification;
use App\Notifications\PrescriptionRejectionNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;

class OrderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
            'prescription_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Upload the prescription image
            $cloudinaryService = app()->make(\App\Customs\Services\CloudinaryService::class);
            $imageResult = $cloudinaryService->uploadImage($request->file('prescription_image'), 'prescriptions');

            // Compute the hash of the image content
            $prescriptionImage = $request->file('prescription_image');
            $imageContent = file_get_contents($prescriptionImage->getRealPath());
            $imageHash = hash('sha256', $imageContent);

            // Log the generated prescription UID
            \Log::info('Generated prescription UID:', ['prescription_uid' => $imageHash]);

            // Check for duplicate prescription or refill eligibility
            $existingOrder = Order::where('prescription_uid', $imageHash)->first();
            if ($existingOrder) {
                if ($existingOrder->refill_allowed <= 0) {
                    return response()->json(['message' => 'This prescription image has already been submitted and cannot be reused.'], 409);
                } else {
                    // Decrement the refill count
                    $existingOrder->refill_allowed -= 1;
                    $existingOrder->save();
                }
            } else {
                // Create a new order only if no existing order is found
                $drug = Drug::findOrFail($request->drug_id);

                if ($drug->stock < $request->quantity) {
                    return response()->json(['message' => "Insufficient stock for drug: {$drug->name}"], 400);
                }

                // Calculate total amount
                $totalAmount = $drug->price * $request->quantity;

                // Create the order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'drug_id' => $request->drug_id,
                    'prescription_uid' => $imageHash, // Use the hash as the unique identifier
                    'prescription_image' => $imageResult['secure_url'], // Path to the prescription image
                    'quantity' => $request->quantity,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                // Reduce stock
                $drug->stock -= $request->quantity;
                $drug->save();

                // Send notification to the pharmacist who created the drug
                $pharmacist = User::find($drug->user_id); // Assuming `user_id` in the `drugs` table refers to the pharmacist
                if ($pharmacist && $pharmacist->is_role === 2) { // Ensure the user is a pharmacist
                    $this->notificationService->sendOrderNotificationToPharmacist($order, $pharmacist);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => $existingOrder ?? $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePrescriptionStatus(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'prescription_status' => 'required|in:approved,rejected,pending', // Validate status
            'refill_allowed' => 'nullable|integer|min:0', // Optional refill count
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Update the prescription status
        $order->prescription_status = $request->input('prescription_status');

        // Update the refill count if provided
        if ($request->has('refill_allowed')) {
            $order->refill_allowed = $request->input('refill_allowed');
        }

        $order->save();

        // Retrieve the prescription associated with the order
        $prescription = Prescription::where('prescription_uid', $order->prescription_uid)->first();

        // Send notifications based on the updated status
        if ($order->prescription_status === 'approved') {
            if ($prescription) {
                $this->notificationService->sendPrescriptionApprovalNotification($order, $prescription);
            }
        } elseif ($order->prescription_status === 'rejected') {
            if ($prescription) {
                $this->notificationService->sendPrescriptionRejectionNotification($order, $prescription);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Prescription status updated successfully',
            'data' => new OrderResource($order),
        ]);
    }

    public function index()
    {
        $orders = Order::with(['drug', 'prescription'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['drug', 'prescription'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json(['data' => $order]);
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
            $query = Order::with(['user:id,name,email,phone,address', 'drug']);

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

            // Filter by drug
            if ($request->has('drug_id')) {
                $query->where('drug_id', $request->drug_id);
            }

            // Filter by minimum amount
            if ($request->has('min_amount')) {
                $query->where('total_amount', '>=', $request->min_amount);
            }

            // Filter by maximum amount
            if ($request->has('max_amount')) {
                $query->where('total_amount', '<=', $request->max_amount);
            }

            // Search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('user', function($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('drug', function($drugQuery) use ($searchTerm) {
                        $drugQuery->where('name', 'like', "%{$searchTerm}%");
                    });
                });
            }

            // Sort orders
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $orders = $query->paginate($perPage);

            // Transform the response to include user details
            $transformedOrders = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'phone' => $order->user->phone,
                        'address' => $order->user->address
                    ],
                    'drug' => [
                        'id' => $order->drug->id,
                        'name' => $order->drug->name,
                        'price' => $order->drug->price
                    ],
                    'quantity' => $order->quantity,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'prescription_image' => $order->prescription_image,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $transformedOrders,
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
        $orders = Order::with(['drug'])->where('user_id', $user->id)->get();
        return OrderResource::collection($orders);
    }
}
