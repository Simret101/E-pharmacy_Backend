<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            // Get the Google user
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists
            $user = User::where('google_id', $googleUser->getId())->first();

            // If user doesn't exist, create a new one
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

            // Generate JWT tokens
            $token = JWTAuth::fromUser($user);
            $refreshToken = JWTAuth::fromUser($user, ['refresh_token' => true]);

            // Prepare user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->is_role,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ];

            // Redirect to frontend with query parameters
            $redirectUrl = 'https://e-pharacy.vercel.app/account/dashboard';
            $query = http_build_query($userData);
            
            return redirect()->away("{$redirectUrl}?{$query}");

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->away('https://e-pharacy.vercel.app/account/dashboard')->with([
                'error' => 'Authentication failed. Please try again.',
                'message' => $e->getMessage()
            ]);
        }
    }
}