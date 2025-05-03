<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Customs\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'refill_allowed' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'prescriptions');
        
        $prescription = Prescription::create([
            'prescription_uid' => Str::uuid(),
            'user_id' => Auth::id(),
            'attachment_path' => $imageResult['secure_url'],
            'refill_allowed' => $request->refill_allowed ?? 1,
            'refill_used' => 0,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Prescription uploaded successfully',
            'data' => $prescription
        ], 201);
    }

    public function update(Request $request, Prescription $prescription)
    {
        if ($prescription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'refill_allowed' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prescriptionData = [
            'refill_allowed' => $request->refill_allowed ?? $prescription->refill_allowed,
        ];

        if ($request->hasFile('image')) {
            if ($prescription->attachment_path) {
                $this->cloudinaryService->deleteImage($prescription->attachment_path);
            }
            
            $imageResult = $this->cloudinaryService->uploadImage($request->file('image'), 'prescriptions');
            $prescriptionData['attachment_path'] = $imageResult['secure_url'];
        }

        $prescription->update($prescriptionData);

        return response()->json([
            'message' => 'Prescription updated successfully',
            'data' => $prescription
        ]);
    }

    public function destroy(Prescription $prescription)
    {
        if ($prescription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($prescription->attachment_path) {
            $this->cloudinaryService->deleteImage($prescription->attachment_path);
        }

        $prescription->delete();

        return response()->json([
            'message' => 'Prescription deleted successfully'
        ]);
    }

    public function dispense($uid)
    {
        if (Auth::user()->is_role !== 2) {
            return response()->json(['message' => 'Only pharmacists can dispense prescriptions'], 403);
        }

        $prescription = Prescription::where('prescription_uid', $uid)->first();

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        if ($prescription->refill_used >= $prescription->refill_allowed) {
            return response()->json(['message' => 'No refills remaining for this prescription'], 400);
        }

        $prescription->update([
            'refill_used' => $prescription->refill_used + 1,
            'status' => $prescription->refill_used + 1 >= $prescription->refill_allowed ? 'fulfilled' : 'partially_filled'
        ]);

        return response()->json([
            'message' => 'Prescription dispensed successfully',
            'data' => $prescription
        ]);
    }
}
