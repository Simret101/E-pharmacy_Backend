<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Http\Resources\DrugResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Customs\Services\CloudinaryService;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryLog;

class DrugController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(Request $request)
    {
        try {
            $query = Drug::query();

            // Apply filters
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }
            if ($request->has('brand')) {
                $query->where('brand', $request->brand);
            }
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
            if ($request->has('in_stock')) {
                $query->where('stock', '>', 0);
            }
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('brand', 'like', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $drugs = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Drugs retrieved successfully',
                'data' => DrugResource::collection($drugs),
                'meta' => [
                    'current_page' => $drugs->currentPage(),
                    'from' => $drugs->firstItem(),
                    'last_page' => $drugs->lastPage(),
                    'per_page' => $drugs->perPage(),
                    'to' => $drugs->lastItem(),
                    'total' => $drugs->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve drugs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $drug = Drug::with(['likes', 'creator'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Drug retrieved successfully',
                'data' => new DrugResource($drug)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Drug not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->is_role !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pharmacists can create drugs.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:1',
            'description' => 'required|string|max:255|min:1',
            'brand' => 'required|string|max:255|min:1',
            'price' => 'required|integer|min:1',
            'category' => 'required|string|max:255|min:1',
            'dosage' => 'required|string|max:255|min:1',
            'stock' => 'required|integer|min:1',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->messages(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'drugs');

            $drugData = $request->except('image');
            $drugData['image'] = $imageResult['secure_url'];
            $drugData['public_id'] = $imageResult['public_id'];
            $drugData['created_by'] = Auth::id();

            $drug = Drug::create($drugData);

            InventoryLog::create([
                'drug_id' => $drug->id,
                'user_id' => Auth::id(),
                'change_type' => 'creation',
                'quantity_changed' => $drug->stock,
                'reason' => 'Drug creation',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Drug created successfully',
                'data' => new DrugResource($drug)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Drug creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create drug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (Auth::user()->is_role !== 2) {
                return response()->json(['message' => 'Only pharmacists can update drugs.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|min:1',
                'description' => 'sometimes|string|max:255|min:1',
                'brand' => 'sometimes|string|max:255|min:1',
                'price' => 'sometimes|numeric|min:1',
                'category' => 'sometimes|string|max:255|min:1',
                'dosage' => 'sometimes|string|max:255|min:1',
                'stock' => 'sometimes|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $drug = Drug::findOrFail($id);

            // Check if user is authorized to update
            if ($drug->created_by !== Auth::id() && Auth::user()->is_role !== 0) {
                return response()->json([
                    'message' => 'Unauthorized to update this drug'
                ], 403);
            }

            // Get the current values before update
            $previousStock = $drug->stock;
            $previousData = $drug->toArray();

            // Prepare update data
            $updateData = [];
            $fields = ['name', 'description', 'brand', 'price', 'category', 'dosage', 'stock'];
            
            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            // Handle image upload if present
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($drug->public_id) {
                    try {
                        $this->cloudinaryService->deleteImage($drug->public_id);
                    } catch (\Exception $e) {
                        \Log::error('Failed to delete old image: ' . $e->getMessage());
                    }
                }
                
                $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'drugs');
                $updateData['image'] = $imageResult['secure_url'];
                $updateData['public_id'] = $imageResult['public_id'];
            }

            // Log the update data for debugging
            \Log::info('Updating drug with data:', $updateData);

            // Update the drug
            $drug->fill($updateData);
            $drug->save();

            // Create inventory log if stock changed
            if (isset($updateData['stock']) && $updateData['stock'] != $previousStock) {
                InventoryLog::create([
                    'drug_id' => $drug->id,
                    'user_id' => Auth::id(),
                    'change_type' => 'stock_update',
                    'quantity_changed' => $updateData['stock'] - $previousStock,
                    'reason' => 'Stock update',
                ]);
            }

            // Refresh the model to get updated data
            $drug->refresh();

            DB::commit();

            return response()->json([
                'message' => 'Drug updated successfully',
                'data' => new DrugResource($drug)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Drug not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Drug update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to update drug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $drug = Drug::findOrFail($id);

            if ($drug->created_by !== Auth::id() && Auth::user()->is_role !== 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this drug'
                ], 403);
            }

            if ($drug->public_id) {
                try {
                    $this->cloudinaryService->deleteImage($drug->public_id);
                } catch (\Exception $e) {
                    \Log::error('Failed to delete drug image from Cloudinary: ' . $e->getMessage());
                }
            }

            $drug->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Drug deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Drug not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete drug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMyDrugs(Request $request)
    {
        $user = Auth::user();

        \Log::info('DrugController@getMyDrugs: Starting method', [
            'user_id' => $user->id,
            'role' => $user->is_role
        ]);

        if ($user->is_role !== 2) {
            return response()->json(['message' => 'Only pharmacists can view their drugs.'], 403);
        }

        try {
            $drugs = Drug::where('created_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($drugs->isEmpty()) {
                return response()->json([
                    'message' => 'No drugs found for this user',
                    'data' => []
                ]);
            }

            return response()->json([
                'message' => 'Drugs retrieved successfully',
                'data' => $drugs
            ]);
        } catch (\Exception $e) {
            \Log::error('DrugController@getMyDrugs: Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error retrieving drugs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function lowStockAlerts()
    {
        $lowStockDrugs = Drug::where('stock', '<', 10)->get(); 

        if ($lowStockDrugs->isEmpty()) {
            return response()->json([
                'message' => 'No low stock drugs found.',
                'data' => []
            ], 200);
        }

        foreach ($lowStockDrugs as $drug) {
            $pharmacist = $drug->creator;
            if ($pharmacist) {
                try {
                    $pharmacist->notify(new \App\Notifications\LowStockAlertNotification($drug));
                    \Log::info('Low stock notification sent to pharmacist', [
                        'pharmacist_id' => $pharmacist->id,
                        'drug_id' => $drug->id,
                        'stock' => $drug->stock
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send low stock notification', [
                        'pharmacist_id' => $pharmacist->id,
                        'drug_id' => $drug->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Low stock notifications sent successfully',
            'data' => DrugResource::collection($lowStockDrugs)
        ]);
    }

    public function adjustStock(Request $request, $id)
    {
        if (Auth::user()->is_role !== 2) {
            return response()->json([ 
                'message' => 'Only pharmacists can manage inventory.' 
            ], 403);
        }

        $drug = Drug::find($id);

        if (!$drug) {
            return response()->json(['message' => 'Drug not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock_change' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $newStock = $drug->stock + $request->stock_change;

            if ($newStock < 0) {
                return response()->json([ 
                    'message' => 'Insufficient stock. Stock cannot go below zero.' 
                ], 400);
            }

            InventoryLog::create([
                'drug_id' => $drug->id,
                'user_id' => Auth::id(),
                'change_type' => 'stock_adjustment',
                'quantity_changed' => $request->stock_change, 
                'reason' => 'Stock adjustment',
            ]);

            $drug->stock = $newStock;
            $drug->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock adjusted successfully.',
                'data' => new DrugResource($drug),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Stock adjustment failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to adjust stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}