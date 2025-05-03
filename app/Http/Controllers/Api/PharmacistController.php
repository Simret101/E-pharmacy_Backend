<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacistResource;
use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PharmacistController extends Controller
{
    
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

        
        if ($pharmacists->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No pharmacists found',
                'data' => []
            ], 404);
        }

        
        return response()->json([
            'status' => 'success',
            'pharmacists' => $pharmacists
        ]);
    }

   
    public function show(User $pharmacist)
    {
        
        if ($pharmacist->is_role !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a pharmacist'
            ], 400);
        }

        
        return response()->json([
            'status' => 'success',
            'data' => new PharmacistResource($pharmacist)
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

    
    public function destroy(User $pharmacist)
    {
        
        if ($pharmacist->is_role !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a pharmacist'
            ], 400);
        }

     
        if ($pharmacist->license_image && Storage::exists('public/' . $pharmacist->license_image)) {
            Storage::delete('public/' . $pharmacist->license_image);
        }

        
        $pharmacist->delete();

        
        return response()->json([
            'status' => 'success',
            'message' => 'Pharmacist deleted successfully'
        ]);
    }
}
