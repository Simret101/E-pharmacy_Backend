<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // For API routes, we need to use stateless authentication
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(Request $request)
    {
        try {
            // Get the Google user
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Check if user exists
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if (!$user) {
                return redirect()->away('https://e-pharacy.vercel.app/auth/signup')
                    ->with('error', 'Please register your pharmacy license first');
            }

            // Create a personal access token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Prepare response data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 2,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration')
            ];

            // Redirect to frontend with query parameters
            $redirectUrl = 'https://e-pharacy.vercel.app/account/dashboard';
            $query = http_build_query($userData);
            
            return redirect()->away("{$redirectUrl}?{$query}");

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->away('https://e-pharacy.vercel.app/auth/signup')
                ->with('error', 'Authentication failed. Please try again.')
                ->with('message', $e->getMessage());
        }
    }
}