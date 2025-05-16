<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PharmacistResource;
use App\Http\Controllers\Api\PharmacistController;
use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PharmacistVerificationStatus;
use App\Notifications\PharmacistStatusUpdated;
use App\Notifications\AdminPharmacistRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    
   

    
    public function viewLicenseImage($id)
    {
        $pharmacist = Pharmacist::find($id);

        if (!$pharmacist) {
            return response()->json([
                'message' => 'Pharmacist not found'
            ], 404);
        }

        if (!$pharmacist->license_image) {
            return response()->json([
                'message' => 'No license image found for this pharmacist'
            ], 404);
        }

        
        return response()->json([
            'message' => 'License image fetched successfully',
            'data' => asset('app/public/' .  $pharmacist->license_image)
        ]);
    }


    public function approvePharmacist(Request $request, $id)
    {
        try {
            $pharmacist = Pharmacist::find($id);

            if (!$pharmacist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pharmacist not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Update pharmacist status
                $pharmacist->status = 'approved';
                $pharmacist->status_reason = 'Documents verified';
                $pharmacist->status_updated_at = now();
                $pharmacist->save();

                // Update user status
                $user = User::find($pharmacist->user_id);
                if ($user) {
                    $user->status = 'approved';
                    $user->save();

                    // Send notification
                    $user->notify(new PharmacistStatusUpdated('approved', 'Documents verified'));
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pharmacist approved successfully',
                    
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error approving pharmacist: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error approving pharmacist'
                    
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error in approvePharmacist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
                
            ], 500);
        }
    }

    
    public function rejectPharmacist(Request $request, $id)
    {
        try {
            $pharmacist = Pharmacist::find($id);

            if (!$pharmacist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pharmacist not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Update pharmacist status
                $pharmacist->status = 'rejected';
                $pharmacist->status_reason = 'Documents not verified';
                $pharmacist->status_updated_at = now();
                $pharmacist->save();

                // Update user status
                $user = User::find($pharmacist->user_id);
                if ($user) {
                    $user->status = 'rejected';
                    $user->save();

                    // Send notification
                    $user->notify(new PharmacistStatusUpdated('rejected', 'Documents not verified'));
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pharmacist rejected successfully',
                  
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error rejecting pharmacist: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error rejecting pharmacist',
                  
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error in rejectPharmacist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
               
            ], 500);
        }
    }

    public function updatePharmacistStatus(Request $request, $id)
    {
        try {
            // Get status from either POST data or query parameters
            $status = $request->input('action') === 'approve' ? 'approved' : 
                     ($request->input('action') === 'reject' ? 'rejected' : 
                     $request->query('status'));

            $reason = $request->input('reason') ?? $request->query('reason') ?? 
                     ($status === 'approved' ? 'Documents verified' : 'Documents not verified');

            if (!$status || !in_array($status, ['approved', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided'
                ], 400);
            }

            $pharmacist = User::where('is_role', 2)->findOrFail($id);

            DB::beginTransaction();

            try {
                // Update the pharmacist's status
                $pharmacist->status = $status;
                $pharmacist->status_reason = $reason;
                $pharmacist->status_updated_at = now();
                $pharmacist->save();

                // Queue the notification instead of sending it immediately
                $pharmacist->notify((new PharmacistStatusUpdated($status, $reason))->delay(now()->addSeconds(5)));

                DB::commit();

                // If it's a GET request (from email link), return a simple success page
                if ($request->method() === 'GET') {
                    $message = $status === 'approved' ? 
                        'Pharmacist has been approved successfully.' : 
                        'Pharmacist has been rejected.';
                    
                    return response()->view('status-update-success', [
                        'message' => $message,
                        'status' => $status
                    ]);
                }

                // For API requests, return JSON
                return response()->json([
                    'success' => true,
                    'message' => 'Pharmacist status updated successfully',
         // Get fresh data from database
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating pharmacist status: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating pharmacist status'
                    
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in updatePharmacistStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
                
            ], 500);
        }
    }

    public function verifyEmail($token)
    {
        try {
            $verification = DB::table('email_verification_tokens')
                ->where('token', $token)
                ->where('expired_at', '>', now())
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification token'
                ], 400);
            }

            $user = User::where('email', $verification->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Update user's email verification status
                $user->email_verified_at = now();
                $user->save();

                // Delete the used token
                DB::table('email_verification_tokens')
                    ->where('token', $token)
                    ->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully. You can now log in to your account.',
                    'data' => [
                        'email' => $user->email,
                        'verified_at' => $user->email_verified_at
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error verifying email: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error verifying email',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in verifyEmail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleEmailAction(Request $request, $id)
    {
        try {
            // Get status from action parameter
            $action = $request->query('action');
            $status = $action === 'approve' ? 'approved' : 
                     ($action === 'reject' ? 'rejected' : null);

            if (!$status || !in_array($status, ['approved', 'rejected'])) {
                return response('Invalid action provided', 400);
            }

            $pharmacist = Pharmacist::find($id);
            if (!$pharmacist) {
                return response('Pharmacist not found', 404);
            }

            DB::beginTransaction();

            try {
                // Update pharmacist status
                $pharmacist->status = $status;
                $pharmacist->status_reason = $status === 'approved' ? 'Documents verified' : 'Documents not verified';
                $pharmacist->status_updated_at = now();
                $pharmacist->save();

                // Update user status (only status field)
                $user = User::find($pharmacist->user_id);
                if ($user) {
                    $user->update(['status' => $status]);
                    $user->notify(new PharmacistStatusUpdated($status, $pharmacist->status_reason));
                }

                DB::commit();

                // Return simple success message
                return response('Pharmacist has been ' . $status . ' successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating pharmacist status: ' . $e->getMessage());
                return response('Error updating pharmacist status', 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in handleEmailAction: ' . $e->getMessage());
            return response('An error occurred', 500);
        }
    }

    public function getPendingPharmacists()
    {
        // Check if the authenticated user is an admin
        if (Auth::user()->is_role !== 0) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can perform this action.'
            ], 403);
        }

        $pendingPharmacists = User::where('is_role', 2)
            ->where('status', 'pending')
            ->paginate(10);

        return response()->json([
            'pharmacists' => $pendingPharmacists
        ]);
    }

    public function getAllPharmacists(Request $request)
    {
        try {
            $query = User::where('is_role', 2); // Get only pharmacists

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by name, email, or pharmacy name
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('pharmacy_name', 'like', "%{$searchTerm}%");
                });
            }

            // Sort by created_at by default, but allow custom sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $pharmacists = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $pharmacists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error fetching pharmacists',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function approvePharmacists(Request $request, $id)
    {
        $statusController = new PharmacistController();
        $response = $statusController->approve($id);
    
        if ($response->getStatusCode() === 200) {
            $user = User::find(Pharmacist::find($id)->user_id);
            if ($user) {
                $user->notify(new PharmacistStatusUpdated('approved', 'Documents verified'));
            }
        }
    
        return $response;
    }
    
    public function rejectPharmacists(Request $request, $id)
    {
        $statusController = new PharmacistController();
        $response = $statusController->reject($id);
    
        if ($response->getStatusCode() === 200) {
            $user = User::find(Pharmacist::find($id)->user_id);
            if ($user) {
                $user->notify(new PharmacistStatusUpdated('rejected', 'Documents not verified'));
            }
        }
    
        return $response;
    }

    public function createAdmin(Request $request)
    {
        // Ensure the authenticated user is an admin
        if (Auth::user()->is_role !== 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized. Only admins can create another admin.'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_role' => 'required|in:0,1,2', // 0 for admin, 1 for patient, 2 for pharmacist
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create the new admin
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_role' => 0, // Set role to admin
                'status' => 'pending', // Set status to active
                'email_verified_at' => now(), // Mark email as verified
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Admin created successfully',
                'data' => $admin
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating admin: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while creating the admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllPatients(Request $request)
    {
        try {
            $query = User::where('is_role', 1);

            // Apply search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%")
                      ->orWhere('address', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Apply sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $patients = $query->paginate($perPage)->withQueryString();


            return response()->json([
                'status' => 'success',
                'message' => 'Patients retrieved successfully',
                'data' => $patients,
                'meta' => [
                    'current_page' => $patients->currentPage(),
                    'from' => $patients->firstItem(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'to' => $patients->lastItem(),
                    'total' => $patients->total(),
                    'active_count' => $patients->where('status', 'active')->count(),
                    'inactive_count' => $patients->where('status', 'inactive')->count()
                ],
                'links' => [
                    'first' => $patients->url(1),
                    'last' => $patients->url($patients->lastPage()),
                    'prev' => $patients->previousPageUrl(),
                    'next' => $patients->nextPageUrl()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPatient($id)
    {
        try {
            $patient = User::where('is_role', 1)->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $patient
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePatientStatus(Request $request, $id)
    {
        try {
            $patient = User::where('is_role', 1)->findOrFail($id);
            $patient->status = $request->status;
            $patient->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Patient status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update patient status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
