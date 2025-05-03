<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrugResource;
use App\Customs\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Drug;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;

class DrugConroller extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(Request $request)
    {
        $drug_query = Drug::query();
        $drug_query->where('stock', '>', 0);
        $search_param = $request->query('q');

        if ($search_param) {
            $drug_query = Drug::search($search_param);
        }

        $drugs = $drug_query->paginate(10);

        if ($drugs->count() > 0) {
            return response()->json([
                'data' => DrugResource::collection($drugs),
                'meta' => [
                    'current_page' => $drugs->currentPage(),
                    'from' => $drugs->firstItem(),
                    'last_page' => $drugs->lastPage(),
                    'per_page' => $drugs->perPage(),
                    'to' => $drugs->lastItem(),
                    'total' => $drugs->total(),
                ],
                'links' => [
                    'first' => $drugs->url(1),
                    'last' => $drugs->url($drugs->lastPage()),
                    'prev' => $drugs->previousPageUrl(),
                    'next' => $drugs->nextPageUrl(),
                ]
            ]);
        } else {
            return response()->json(['message' => 'No record available'], 200);
        }
    }

   

    public function getMyDrugs()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $drugs = Drug::where('created_by', $user->id)
                        ->get();

            return response()->json([
                'status' => 'success',
                'data' => $drugs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error fetching drugs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        if (Auth::user()->is_role !== 2) {
            return response()->json(['message' => 'Only pharmacists can create drugs.'], 403);
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
                'message' => 'Validation error',
                'errors' => $validator->messages(),
            ], 422);
        }

        $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'drugs');
        
        $drugData = $request->except('image');
        $drugData['image'] = $imageResult['secure_url'];
        $drugData['created_by'] = Auth::id();

        $drug = Drug::create($drugData);

        InventoryLog::create([
            'drug_id' => $drug->id,
            'user_id' => Auth::id(),
            'change_type' => 'creation', 
            'quantity_changed' => $drug->stock, 
            'reason' => 'Drug creation',
        ]);

        return response()->json([
            'message' => 'Drug Created successfully',
            'data' => new DrugResource($drug)
        ], 201);
    }

    public function update(Request $request, Drug $drug)
    {
        if (Auth::user()->is_role !== 2) {
            return response()->json(['message' => 'Only pharmacists can update drugs.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:1',
            'description' => 'required|string|max:255|min:1',
            'brand' => 'required|string|max:255|min:1',
            'price' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'category' => 'required|string|max:255|min:1',
            'dosage' => 'required|string|max:255|min:1',
            'stock' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->messages(),
            ], 422);
        }

        $drugData = $request->except('image');

        if ($request->hasFile('image')) {
            $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'drugs');
            $drugData['image'] = $imageResult['secure_url'];
        }

        $previousStock = $drug->stock;
        $drug->update($drugData);

        InventoryLog::create([
            'drug_id' => $drug->id,
            'user_id' => Auth::id(),
            'change_type' => 'stock_update',
            'quantity_changed' => $drug->stock - $previousStock, 
            'reason' => 'Stock update',
        ]);

        return response()->json([
            'message' => 'Drug updated successfully',
            'data' => new DrugResource($drug)
        ], 200);
    }

    public function show(Drug $drug)
    {
        return response()->json([
            'data' => new DrugResource($drug)
        ]);
    }

    public function destroy(Drug $drug)
    {
        if ($drug->public_id) {
            $this->cloudinaryService->deleteImage($drug->public_id);
        }

        InventoryLog::create([
            'drug_id' => $drug->id,
            'user_id' => Auth::id(),
            'change_type' => 'deletion',
            'quantity_changed' => 0, 
            'reason' => 'Drug deletion',
        ]);

        $drug->delete();
        return response()->json([
            'message' => 'Drug Deleted successfully',
        ], 200);
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

        // Notify and email each pharmacist for their low-stock drugs
        foreach ($lowStockDrugs as $drug) {
            $pharmacist = $drug->creator;
            if ($pharmacist) {
                try {
                    // Send notification (database and email)
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
            'data' => \App\Http\Resources\DrugResource::collection($lowStockDrugs)
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

        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'data' => new DrugResource($drug),
        ], 200);
    }
    
}
