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
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

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

            // Log the user in
            Auth::login($user);

            // Redirect to home with success message
            return redirect()->route('home')->with('success', 'Successfully logged in with Google');

        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            // Redirect back with error message instead of JSON response
            return redirect()->back()->with('error', 'Authentication failed. Please try again.');
        }
    }
}
