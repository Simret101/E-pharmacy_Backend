<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockAlertMail;
use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Http\Resources\DrugResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Customs\Services\CloudinaryService;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Log;

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
            $drugs = $query->with(['creator'])->paginate($perPage)->withQueryString();

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
                    'total_stock' => $drugs->sum('stock'),
                    'low_stock_count' => $drugs->where('stock', '<', 10)->count()
                ], 'links' => [
                    'first' => $drugs->url(1),
                    'last' => $drugs->url($drugs->lastPage()),
                    'prev' => $drugs->previousPageUrl(),
                    'next' => $drugs->nextPageUrl()
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
            $drug = Drug::with(['creator'])->findOrFail($id);

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

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:1',
                'brand' => 'required|string|max:255|min:1',
                'description' => 'required|string|max:255|min:1',
                'category' => 'required|string|max:255|min:1',
                'price' => 'required|numeric|min:1',
                'stock' => 'required|integer|min:1',
                'dosage' => 'required|string|max:255|min:1',
                'prescription_needed' => 'required|in:0,1,true,false',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'expires_at' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            // Convert prescription_needed to boolean if it's a string
            if (isset($data['prescription_needed'])) {
                $data['prescription_needed'] = filter_var($data['prescription_needed'], FILTER_VALIDATE_BOOLEAN);
            }
            $data['created_by'] = Auth::id();

            if ($request->hasFile('image')) {
                try {
                    $image = $request->file('image');
                    $result = $this->cloudinaryService->uploadImage($image, 'drugs');
                    $data['image'] = $result['secure_url'];
                    $data['public_id'] = $result['public_id'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to upload image',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            $data['created_by'] = Auth::id();

            $drug = Drug::create($data);

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
                'data' => new DrugResource($drug->load('creator'))
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

            // Separate form data and file
            $formData = $request->except(['image']);
            
            // Validate form data
            $validator = Validator::make($formData, [
                'name' => 'sometimes|string|max:255|min:1',
                'description' => 'sometimes|string|max:255|min:1',
                'brand' => 'sometimes|string|max:255|min:1',
                'price' => 'sometimes|numeric|min:1',
                'category' => 'sometimes|string|max:255|min:1',
                'dosage' => 'sometimes|string|max:255|min:1',
                'stock' => 'sometimes|integer|min:0',
                'prescription_needed' => 'sometimes|in:0,1,true,false',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $drug = Drug::findOrFail($id);

            if ($drug->created_by !== Auth::id() && Auth::user()->is_role !== 0) {
                return response()->json([
                    'message' => 'Unauthorized to update this drug'
                ], 403);
            }

            $previousStock = $drug->stock;

            // Prepare update data
            $updateData = $validator->validated();
            
            // Handle prescription_needed conversion
            if (isset($updateData['prescription_needed'])) {
                $updateData['prescription_needed'] = filter_var($updateData['prescription_needed'], FILTER_VALIDATE_BOOLEAN);
            }

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                // Validate the image file
                $imageValidator = Validator::make(['image' => $image], [
                    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
                ]);

                if ($imageValidator->fails()) {
                    return response()->json([
                        'message' => 'Invalid image file',
                        'errors' => $imageValidator->errors()
                    ], 422);
                }

                // Delete old image if exists
                if ($drug->public_id) {
                    try {
                        $this->cloudinaryService->deleteImage($drug->public_id);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old image:', [
                            'error' => $e->getMessage(),
                            'drug_id' => $id
                        ]);
                    }
                }

                // Upload new image
                $result = $this->cloudinaryService->uploadImage($image, 'drugs');
                $updateData['image'] = $result['secure_url'];
                $updateData['public_id'] = $result['public_id'];
            }

            // Add updated_by
            $updateData['updated_by'] = Auth::id();

        // Update the drug with prepared data
        $drug->update($updateData);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                
                // Validate image size
                if ($image->getSize() > 2048 * 1024) {
                    return response()->json([
                        'message' => 'Image too large. Maximum size is 2MB.',
                        'error' => 'image_too_large'
                    ], 422);
                }

                // Delete old image if exists
                if ($drug->public_id) {
                    try {
                        $this->cloudinaryService->deleteImage($drug->public_id);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old image: ' . $e->getMessage());
                    }
                }
                
                // Upload new image
                $imageResult = $this->cloudinaryService->uploadImage($image, 'drugs');
                $updateData['image'] = $imageResult['secure_url'];

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed to upload image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Log the final update data
        \Log::info('Final update data:', $updateData);

        // Log the final update data
        \Log::info('Final update data:', $updateData);

        // Update the drug using mass assignment
        try {
            $drug->fill($updateData);
            $drug->save();
            \Log::info('Drug updated successfully');

            // Create inventory log if stock changed
            if (isset($allData['stock']) && $allData['stock'] != $previousStock) {
                InventoryLog::create([
                    'drug_id' => $drug->id,
                    'user_id' => Auth::id(),
                    'change_type' => 'update',
                    'quantity_changed' => $allData['stock'] - $previousStock,
                    'reason' => 'Stock updated from ' . $previousStock . ' to ' . $allData['stock']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Drug updated successfully',
                'data' => new DrugResource($drug->load('creator'))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update drug:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'drug_id' => $drug->id,
                'update_data' => $allData
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update drug',
                'error' => $e->getMessage()
            ], 500);
        }

        // Update the drug using mass assignment
        try {
            $drug->fill($updateData);
            $drug->save();
            \Log::info('Drug updated successfully');

            // Create inventory log if stock changed
            if (isset($updateData['stock']) && $updateData['stock'] != $previousStock) {
                InventoryLog::create([
                    'drug_id' => $drug->id,
                    'user_id' => Auth::id(),
                    'change_type' => 'update',
                    'quantity_changed' => $updateData['stock'] - $previousStock,
                    'reason' => 'Stock updated from ' . $previousStock . ' to ' . $updateData['stock']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Drug updated successfully',
                'data' => new DrugResource($drug->load('creator'))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update drug:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'drug_id' => $drug->id,
                'update_data' => $updateData
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update drug',
                'error' => $e->getMessage()
            ], 500);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Drug update failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update drug',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getDrugsByCreator(Request $request, $username)
{
    try {
        $query = Drug::whereHas('creator', function($query) use ($username) {
            $query->where('username', $username);
        })->with('creator');

        // Apply search
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
        $drugs = $query->paginate($perPage)->withQueryString();

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
                'total_stock' => $drugs->sum('stock'),
                'low_stock_count' => $drugs->where('stock', '<', 10)->count()
            ],
            'links' => [
                'first' => $drugs->url(1),
                'last' => $drugs->url($drugs->lastPage()),
                'prev' => $drugs->previousPageUrl(),
                'next' => $drugs->nextPageUrl()
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
public function getByCategory(Request $request, $category)
{
    try {
        $query = Drug::with(['creator'])->byCategory($category);

        // Apply search
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
        $drugs = $query->paginate($perPage)->withQueryString();

        if ($drugs->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No drugs found in this category',
                'data' => []
            ], 200);
        }

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
                'total_stock' => $drugs->sum('stock'),
                'low_stock_count' => $drugs->where('stock', '<', 10)->count()
            ],
            'links' => [
                'first' => $drugs->url(1),
                'last' => $drugs->url($drugs->lastPage()),
                'prev' => $drugs->previousPageUrl(),
                'next' => $drugs->nextPageUrl()
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
            $query = Drug::where('created_by', $user->id);

            // Apply search
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
            $drugs = $query->paginate($perPage)->withQueryString();

            if ($drugs->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No drugs found for this user',
                    'data' => []
                ], 200);
            }

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
                    'total_stock' => $drugs->sum('stock'),
                    'low_stock_count' => $drugs->where('stock', '<', 10)->count()
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('DrugController@getMyDrugs: Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
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