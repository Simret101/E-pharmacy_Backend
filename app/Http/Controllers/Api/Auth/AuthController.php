<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistraationRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use App\Customs\Services\EmailVerificationService;
use App\Http\Resources\PharmacistResource;
use App\Http\Resources\PatientResource;
use App\Services\AdminEmailService;
use App\Customs\Services\CloudinaryService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Services\NotificationService;

class AuthController extends Controller
{
    public function __construct(
        private EmailVerificationService $service,
        private AdminEmailService $adminEmailService,
        private CloudinaryService $cloudinaryService,
        private NotificationService $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

  
    public function register(RegistraationRequest $request)
    {
        $data = $request->validated();
    
        // Prevent admin registration
        if ($request->input('is_role') === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin registration is not allowed .'
            ], 403);
        }

        // Ensure role is either 1 (patient) or 2 (pharmacist)
        $data['is_role'] = $request->input('is_role', 1);
        if (!in_array($data['is_role'], [1, 2])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role specified. Only patients and pharmacists  can register.'
            ], 422);
        }
        
        if ($request->hasFile('license_image')) {
            $result = $this->cloudinaryService->uploadImage($request->file('license_image'), 'licenses');
            $data['license_image'] = $result['secure_url'];
        }
    
        if ($request->hasFile('tin_image')) {
            $result = $this->cloudinaryService->uploadImage($request->file('tin_image'), 'tin_documents');
            $data['tin_image'] = $result['secure_url'];
        }
    
        if ($request->filled('place_name') && $request->filled('lat') && $request->filled('lng')) {
            $data['place_name'] = $request->input('place_name');
            $data['address'] = $request->input('address');
            $data['status'] = 'pending';
            $data['lat'] = $request->input('lat');
            $data['lng'] = $request->input('lng');
        }
    
        $user = User::create($data);
        
        // Send verification email
        $this->service->sendVerificationLink($user);
        
        // If user is a pharmacist, send notification to admin
        if ($user->is_role == 2) {
            $this->adminEmailService->sendNewPharmacistNotification($user);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please verify your email.',
            
        ], 201);
    }
    

 
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        if ($user->is_role == 2) {
            if ($user->status == 'pending') {
                // Send pending status notification using notification service
                $this->notificationService->sendPharmacistRegistrationStatusNotification(
                    $user, 
                    'pending', 
                    'Your pharmacist registration is pending approval.'
                );
                return response()->json([
                    'status' => 'failed', 
                    'message' => 'Your license is not verified yet. Please wait for approval.',
                    'data' => [
                        'status' => 'pending',
                        'message' => 'Your license is not verified yet. Please wait for approval.'
                    ]
                ], 403);
            }
    
            if ($user->status == 'rejected') {
                // Send rejected status notification using notification service
                $this->notificationService->sendPharmacistRegistrationStatusNotification($user, 'rejected');
                return response()->json([
                    'status' => 'failed', 
                    'message' => 'Your license has been declined. You are not allowed to log in.',
                    'data' => [
                        'status' => 'rejected',
                        'message' => 'Your license has been declined. You are not allowed to log in.'
                    ]
                ], 403);
            }
        }
    
        if (!$user->email_verified_at) {
            return response()->json(['status' => 'failed', 'message' => 'Please verify your email before logging in.'], 403);
        }

        // Generate a refresh token
        $refreshToken = Hash::make(now());

        // Store the refresh token in the database
        DB::table('refresh_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            ['token' => $refreshToken, 'expires_at' => now()->addDay()]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'pharmacy_name' => $user->pharmacy_name,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'lat' => $user->lat,
                    'lng' => $user->lng,
                    'account_number' => $user->account_number,
                    'bank_name' => $user->bank_name,
                ],
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]
        ], 200);
    }
    

    public function refreshToken(Request $request)
    {
        try {
            // Attempt to authenticate the user from the token
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $refreshToken = $request->input('refresh_token');

            // Retrieve the stored refresh token for the user
            $storedToken = DB::table('refresh_tokens')->where('user_id', $user->id)->first();

            // Validate the refresh token
            if (!$storedToken || !Hash::check($refreshToken, $storedToken->token)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid refresh token.'
                ], 401);
            }

            // Check if the refresh token has expired
            if (Carbon::parse($storedToken->expires_at)->isPast()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Refresh token expired. Please log in again.'
                ], 401);
            }

            // Generate a new access token and refresh token
            $newAccessToken = Auth::login($user);
            $newRefreshToken = Hash::make(now());

            // Update the refresh token in the database
            DB::table('refresh_tokens')->where('user_id', $user->id)->update([
                'token' => $newRefreshToken,
                'expires_at' => now()->addDay()
            ]);

            // Return the new tokens
            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Access token has expired. Please log in again.',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid access token.',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Access token is missing or improperly formatted.',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while refreshing the token.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->service->verifyEmail($request->email, $request->token);
    }

    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        return $this->service->resendLink($request->email);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));
        return response()->json(['status' => $status === Password::RESET_LINK_SENT ? 'success' : 'failed', 'message' => __($status)]);
    }




    public function profile()
    {
        try {
            // Log the token
            Log::info('Token:', ['token' => JWTAuth::getToken()]);

            // Retrieve the authenticated user using JWTAuth
            $user = JWTAuth::parseToken()->authenticate();

            // Log the authenticated user
            Log::info('Authenticated user:', ['user' => $user]);

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Return the user data
            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => $user
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized: Access token has expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized: Access token is invalid'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Error in profile method: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    public function logout()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if ($user) {
                // Delete the user's refresh token
                DB::table('refresh_tokens')
                    ->where('user_id', $user->id)
                    ->delete();
                
                // Revoke all user tokens
                $user->tokens()->delete();
                
                // Logout the user
                Auth::logout();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'User has been logged out successfully'
                ]);
            }
            
            return response()->json([
                'status' => 'failed',
                'message' => 'No authenticated user found'
            ], 401);
            
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred during logout'
            ], 500);
        }
    }
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate user role (0: Admin, 1: Patient, 2: Pharmacist)
            if (!in_array($user->is_role, [0, 1, 2])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid user role'
                ], 400);
            }

            // Different validation rules based on user role
            $rules = [
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'lat' => 'sometimes|numeric|between:-90,90',
                'lng' => 'sometimes|numeric|between:-180,180',
            ];

            // Add pharmacist-specific rules
            if ($user->is_role === 2) { // Pharmacist
                $rules['pharmacy_name'] = 'sometimes|string|max:255';
                $rules['tin_number'] = 'sometimes|string|max:50';
                $rules['bank_name'] = 'sometimes|string|max:255';
                $rules['account_number'] = 'sometimes|string|max:50';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Only update the validated fields
            $validatedData = $validator->validated();
            
            // Remove any attempt to update email, license_image, or tin_image
            unset($validatedData['email']);
            unset($validatedData['license_image']);
            unset($validatedData['tin_image']);
            unset($validatedData['is_role']); // Prevent role changes
            unset($validatedData['status']); // Prevent status changes
            
            // Log the data being updated
            Log::info('Updating user profile', [
                'user_id' => $user->id,
                'data' => $validatedData
            ]);
            
            // Update the user
            $updated = $user->update($validatedData);

            if (!$updated) {
                Log::error('Failed to update user profile', [
                    'user_id' => $user->id,
                    'data' => $validatedData
                ]);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to update profile'
                ], 500);
            }

            // Refresh the user model
            $user = $user->fresh();

            // Log the updated user data
            Log::info('User profile updated successfully', [
                'user_id' => $user->id,
                'updated_data' => $user->toArray()
            ]);

            // Return appropriate resource based on user role
            if ($user->is_role === 2) { // Pharmacist
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => new PharmacistResource($user)
                ]);
            } else { // Admin or Patient
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => new PatientResource($user)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
   
    public function getProfile()
    {
        try {
            // Retrieve the authenticated user
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Return the user data based on their role
            if ($user->is_role === 1) { // Admin
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile retrieved successfully',
                    'user' => new AdminResource($user)
                ]);
            } elseif ($user->is_role === 2) { // Pharmacist
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile retrieved successfully',
                    'user' => new PharmacistResource($user)
                ]);
            } else { // Patient
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile retrieved successfully',
                    'user' => new PatientResource($user)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving profile: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyAccessToken()
    {
        try {
            // Attempt to authenticate the user using the token
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized: Access token is invalid or expired'
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Access token is valid',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized: Access token has expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized: Access token is invalid'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while verifying the access token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
