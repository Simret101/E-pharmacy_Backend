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
use App\Notifications\PrescriptionEmailApprovalNotification;
use App\Notifications\OrderStatusNotification;
use App\Notifications\PrescriptionApprovalNotification;
use App\Notifications\PrescriptionRejectionNotification;
use App\Notifications\OrderShippedNotification;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderReviewNotification;
use App\Notifications\PharmacistOrderShippedNotification;
use App\Notifications\PharmacistOrderDeliveredNotification;
use App\Notifications\PharmacistOrderCancelledNotification;
use App\Notifications\PharmacistPaymentReceivedNotification;
use App\Services\PrescriptionNotificationService;
use App\Mail\PrescriptionReviewMail;
use App\Mail\PrescriptionDecisionMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;

class OrderController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private PrescriptionNotificationService $prescriptionNotificationService
    ) {
        $this->notificationService = $notificationService;
         $this->prescriptionNotificationService = $prescriptionNotificationService;
    }

   // In app/Http/Controllers/Api/OrderController.php
   public function store(Request $request)
   {
       $validator = Validator::make($request->all(), [
           'drug_id' => 'required|exists:drugs,id',
           'quantity' => 'required|integer|min:1',
           'prescription_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
           'refill_allowed' => 'nullable|integer|min:0'
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
   
           // Check for duplicate prescription or refill eligibility
           $existingOrder = Order::where('prescription_uid', $imageHash)->first();
           if ($existingOrder) {
               if ($existingOrder->refill_allowed <= 0) {
                   return response()->json(['message' => 'This prescription image has already been submitted and cannot be reused.'], 409);
               } else {
                   // Decrement the refill count
                   $existingOrder->refill_allowed -= 1;
                   $existingOrder->refill_used += 1;
                   $existingOrder->save();
                   return response()->json([
                       'success' => true,
                       'message' => 'Order created successfully using refill',
                       'data' => new OrderResource($existingOrder)
                   ]);
               }
           }
   
           $drug = Drug::findOrFail($request->drug_id);
   
           // Check if stock is less than 10
           if ($drug->stock < 10) {
               return response()->json([
                   'success' => false,
                   'message' => "Low stock alert: Only {$drug->stock} units of {$drug->name} available. Please contact the pharmacist.",
                   'remaining_stock' => $drug->stock
               ], 400);
           }
   
           if ($drug->stock < $request->quantity) {
               return response()->json(['message' => "Insufficient stock for drug: {$drug->name}"], 400);
           }
   
           // Calculate total amount
           $totalAmount = $drug->price * $request->quantity;
   
           // Create the order
           $order = Order::create([
               'user_id' => Auth::id(),
               'drug_id' => $request->drug_id,
               'prescription_uid' => $imageHash,
               'prescription_image' => $imageResult['secure_url'],
               'quantity' => $request->quantity,
               'total_amount' => $totalAmount,
               'status' => 'pending',
               'prescription_status' => 'pending',
               'refill_allowed' => $request->input('refill_allowed', 0),
               'refill_used' => 0
           ]);
   
           // Create inventory log
           InventoryLog::create([
               'drug_id' => $request->drug_id,
               'user_id' => Auth::id(),
               'change_type' => 'sale',
               'quantity_changed' => -$request->quantity,
               'reason' => 'Sale made through order',
               'order_id' => $order->id
           ]);
   
           // Reduce stock
           $drug->stock -= $request->quantity;
           $drug->save();
   
           // Send notifications
           $user = Auth::user();
           if ($user) {
               $this->notificationService->sendOrderCreatedNotification($user, $order, 'Your order has been created successfully. We will process it shortly.');
           }
   
           // Notify pharmacist about new order
           $pharmacist = User::find($order->drug->created_by);
           if ($pharmacist) {
               $this->notificationService->sendPrescriptionReviewNotification($pharmacist, $order, 'A new prescription requires your review. Please check the details.');
           }
   
           DB::commit();
   
           return response()->json([
               'success' => true,
               'message' => 'Order created successfully',
               'data' => new OrderResource($order)
           ]);
   
       } catch (\Exception $e) {
           DB::rollBack();
           \Log::error('Error creating order: ' . $e->getMessage());
           return response()->json([
               'success' => false,
               'message' => 'Error creating order: ' . $e->getMessage()
           ], 500);
       }
   }
public function getPharmacistOrderById($id)
{
    try {
        $pharmacist = auth()->user();

        // Get all drugs created by this pharmacist
        $pharmacistDrugs = Drug::where('created_by', $pharmacist->id)->pluck('id');

        // Get the specific order
        $order = Order::with(['drug', 'prescription', 'user'])
            ->whereIn('drug_id', $pharmacistDrugs)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not accessible'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order retrieved successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve order: ' . $e->getMessage()
        ], 500);
    }
}
// Add this method to handle prescription decisions
public function updatePrescriptionStatus(Request $request, $orderId)
{
    $validator = Validator::make($request->all(), [
        'prescription_status' => 'required|in:approved,rejected,pending',
        'refill_allowed' => 'nullable|integer|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        DB::beginTransaction();

        $user = auth()->user();
        if ($user->is_role !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Only pharmacists can update prescription status'
            ], 403);
        }

        $order = Order::with('drug')->findOrFail($orderId);
        
        // Check if the drug was created by this pharmacist
        if ($order->drug->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update prescriptions for drugs you created'
            ], 403);
        }

        // Update prescription status and refill count
        $order->prescription_status = $request->prescription_status;
        if ($request->has('refill_allowed')) {
            $order->refill_allowed = $request->refill_allowed;
        }

        // Add logging
        \Log::info('Prescription status update', [
            'order_id' => $orderId,
            'status' => $request->prescription_status,
            'refill_allowed' => $request->refill_allowed ?? null
        ]);

        $order->save();

        // Send notifications based on the decision
        $user = $order->user;
        if ($user) {
            $message = match($order->prescription_status) {
                'approved' => 'Your prescription has been approved. Your order will be processed shortly.',
                'rejected' => 'Your prescription has been rejected. Please contact customer support for more information.',
                'pending' => 'Your prescription is still under review.'
            };

            $this->notificationService->sendPrescriptionDecisionNotification($user, $order, $order->prescription_status, $message);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Prescription status updated successfully',
            'data' => new OrderResource($order)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Prescription status update failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'order_id' => $orderId
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to update prescription status',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function index(Request $request)
    {
        try {
            $query = Order::with(['drug', 'prescription'])->where('user_id', Auth::id());

            // Apply search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('drug', function($drugQuery) use ($searchTerm) {
                        $drugQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('description', 'like', "%{$searchTerm}%")
                                ->orWhere('brand', 'like', "%{$searchTerm}%");
                    })
                    ->orWhere('prescription_uid', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Apply sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $orders = $query->paginate($perPage)->withQueryString();

            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $orders,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'from' => $orders->firstItem(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'to' => $orders->lastItem(),
                    'total' => $orders->total(),
                    'total_amount' => $orders->sum('total_amount'),
                    'pending_count' => $orders->where('status', 'pending')->count(),
                    'completed_count' => $orders->where('status', 'completed')->count()
                ],
                'links' => [
                    'first' => $orders->url(1),
                    'last' => $orders->url($orders->lastPage()),
                    'prev' => $orders->previousPageUrl(),
                    'next' => $orders->nextPageUrl()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getPharmacistOrders(Request $request)
{
    $user = auth()->user();

    \Log::info('OrderController@getPharmacistOrders: Starting method', [
        'user_id' => $user->id,
        'role' => $user->is_role
    ]);

    if ($user->is_role !== 2) {
        return response()->json(['message' => 'Only pharmacists can view their orders.'], 403);
    }

    try {
        // Get drugs created by this pharmacist
        $pharmacistDrugs = Drug::where('created_by', $user->id)->pluck('id');

        $query = Order::with(['drug', 'prescription', 'user'])
            ->whereIn('drug_id', $pharmacistDrugs);

        // Apply search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('drug', function($drugQuery) use ($searchTerm) {
                    $drugQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%")
                            ->orWhere('brand', 'like', "%{$searchTerm}%");
                })
                ->orWhere('prescription_uid', 'like', "%{$searchTerm}%");
            });
        }

        // Apply filters
       
        if ($request->has('prescription_status')) {
            $query->where('prescription_status', $request->prescription_status);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('category_id')) {  // Add this
            $query->whereHas('drug', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->per_page ?? 10;
        $orders = $query->paginate($perPage)->withQueryString();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No orders found for this pharmacist',
                'data' => []
            ], 200);
        }

        // Calculate additional statistics
        $totalRevenue = $orders->filter(function ($order) {
        return $order->status === 'paid';  // Only include paid orders
        })->sum(function ($order) {
        return $order->drug->price * $order->quantity;
        });

        $pendingOrders = $orders->where('status', 'pending')->count();
        $completedOrders = $orders->where('status', 'completed')->count();
        $paidOrders = $orders->where('status', 'paid')->count();  // Add this

        return response()->json([
        'status' => 'success',
        'message' => 'Orders retrieved successfully',
        'data' => OrderResource::collection($orders),
        'meta' => [
            'total' => $orders->total(),
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'paid_orders' => $paidOrders,  // Add this
        ]
        ], 200);

    } catch (\Exception $e) {
        \Log::error('OrderController@getPharmacistOrders: Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Error retrieving orders',
            'error' => $e->getMessage()
        ], 500);
    }
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

    public function userOrders(Request $request)
{
    try {
        $user = auth()->user();

        $query = Order::with(['drug', 'prescription', 'user'])
            ->where('user_id', $user->id);

        // Apply status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply search
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('drug', function($drugQuery) use ($searchTerm) {
                    $drugQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('description', 'like', "%{$searchTerm}%")
                            ->orWhere('brand', 'like', "%{$searchTerm}%");
                })
                ->orWhere('prescription_uid', 'like', "%{$searchTerm}%");
            });
        }

        // Apply date filters
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $orders = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'from' => $orders->firstItem(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'to' => $orders->lastItem(),
                'total' => $orders->total(),
                'total_amount' => $orders->sum('total_amount'),
                'pending_count' => $orders->where('status', 'pending')->count(),
                'completed_count' => $orders->where('status', 'completed')->count()
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve orders',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function approvePrescription(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user = $order->user;
        
        // Update prescription status
        $order->prescription_status = 'approved';
        $order->save();
        
        // Send notification
        $this->prescriptionNotificationService->sendPrescriptionDecisionNotification(
            $user,
            $order,
            'approved',
            'Your prescription has been approved. Your order will be processed shortly.'
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Prescription approved successfully',
            'data' => new OrderResource($order)
        ]);
    }
public function rejectPrescription(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Update prescription status
    $order->prescription_status = 'rejected';
    $order->save();
    
    // Send notification to user
    $user = $order->user;
    if ($user) {
        $this->notificationService->sendPrescriptionDecisionNotification(
            $user,
            $order,
            'rejected',
            'Your prescription has been rejected. Please contact customer support for more information.'
        );
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Prescription rejected successfully',
        'data' => new OrderResource($order)
    ]);
}

public function updateRefill(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'refill_allowed' => 'required|integer|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $order = Order::findOrFail($id);
    
    // Update refill allowed count
    $order->refill_allowed = $request->refill_allowed;
    $order->save();
    
    // Send notification to user
    $user = $order->user;
    if ($user) {
        $this->notificationService->sendPrescriptionDecisionNotification(
            $user,
            $order,
            'refill_updated',
            'Your prescription refill allowance has been updated.'
        );
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Refill allowance updated successfully',
        'data' => new OrderResource($order)
    ]);
}
}
