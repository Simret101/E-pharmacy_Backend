<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacistResource;
use App\Models\Pharmacist;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;

class PharmacistController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $pharmacists = User::where('is_role', 2);  

        $search_param = $request->query('search');
        if ($search_param) {
            $pharmacists->where(function($query) use ($search_param) {
                $query->where('name', 'LIKE', "%{$search_param}%")
                    ->orWhere('address', 'LIKE', "%{$search_param}%")
                    ->orWhere('phone', 'LIKE', "%{$search_param}%")
                    ->orWhere('email', 'LIKE', "%{$search_param}%");
            });
        }

        $pharmacists = $pharmacists->get();

        return response()->json([
            'status' => 'success',
            'data' => $pharmacists
        ]);
    }

    public function show($id)
    {
        $pharmacist = User::where('id', $id)
            ->where('is_role', 2)
            ->first();

        if (!$pharmacist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pharmacist not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pharmacist
        ]);
    }

    public function getByUsername(Request $request, $username)
    {
        $pharmacist = User::where('username', $username)
            ->where('is_role', 2)
            ->first();

        if (!$pharmacist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pharmacist not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pharmacist
        ]);
    }

    public function getPatient(Request $request, $patientId)
    {
        $patient = User::where('id', $patientId)
            ->where('is_role', 1)
            ->first();

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient
        ]);
    }

    public function update(Request $request, User $pharmacist)
    {
        if ($pharmacist->is_role !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a pharmacist'
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users,email,' . $pharmacist->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'license_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  
        ]);

        if ($request->hasFile('license_image')) {
            if ($pharmacist->license_image && Storage::exists('public/' . $pharmacist->license_image)) {
                Storage::delete('public/' . $pharmacist->license_image);
            }

            $licenseImagePath = $request->file('license_image')->store('licenses', 'public');
            $pharmacist->license_image = $licenseImagePath;
        }

    
        $pharmacist->update($validated);

        
        return response()->json([
            'status' => 'success',
            'message' => 'Pharmacist updated successfully',
            'data' => new PharmacistResource($pharmacist)
        ]);
    }

    


    public function approve($id)
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update user status
            $user->status = 'approved';
            $user->save();

            // Send notification
            $this->notificationService->sendPharmacistRegistrationStatusNotification(
                $user, 
                'approved', 
                'Your pharmacist registration has been approved.'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pharmacist approved successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving pharmacist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving pharmacist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update user status
            $user->status = 'rejected';
            $user->save();

            // Send notification
            $this->notificationService->sendPharmacistRegistrationStatusNotification(
                $user, 
                'rejected', 
                'Your pharmacist registration has been rejected.'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pharmacist rejected successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error rejecting pharmacist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting pharmacist: ' . $e->getMessage()
            ], 500);
        }
    }

}