<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ]);
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                    'is_role' => 1 // Default to patient role
                ]);
            }

            // Generate JWT token
            $token = Auth::login($user);
            
            // Generate refresh token
            $refreshToken = Hash::make(now());
            
            // Store refresh token
            DB::table('refresh_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'token' => $refreshToken,
                    'expires_at' => Carbon::now()->addDay()
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer'
            ]);

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Authentication failed. Please try again.'
            ], 401);
        }
    }
}
