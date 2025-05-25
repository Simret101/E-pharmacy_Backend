<?php
namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
class PasswordController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users'
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No account found'
                ], 404);
            }

            $token = Str::random(64);
            
            PasswordReset::create([
                'email' => $request->email,
                'token' => hash('sha256', $token)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Token generated successfully',
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to generate token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'token' => 'required|string'
        ]);

        try {
            $hashedToken = hash('sha256', $request->token);
            $passwordReset = PasswordReset::where([
                'email' => $request->email,
                'token' => $hashedToken
            ])->first();

            if (!$passwordReset) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid token'
                ], 401);
            }

            if ($passwordReset->created_at) {
                $expiryTime = $passwordReset->created_at->addMinutes(5);
                if ($expiryTime->isPast()) {
                    $passwordReset->delete();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Token has expired'
                    ], 401);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Token verified successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to verify token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed'
        ]);

        try {
            $hashedToken = hash('sha256', $request->token);
            $passwordReset = PasswordReset::where([
                'email' => $request->email,
                'token' => $hashedToken
            ])->first();

            if (!$passwordReset) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid token'
                ], 401);
            }

            if ($passwordReset->created_at) {
                $expiryTime = $passwordReset->created_at->addMinutes(5);
                if ($expiryTime->isPast()) {
                    $passwordReset->delete();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Token has expired'
                    ], 401);
                }
            }

            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the password reset token after successful password change
            $passwordReset->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to reset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function changePassword(ChangePasswordRequest $request)
{
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not authenticated'
            ], 401);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Failed to update password',
            'error' => $e->getMessage()
        ], 500);
    }
}
}