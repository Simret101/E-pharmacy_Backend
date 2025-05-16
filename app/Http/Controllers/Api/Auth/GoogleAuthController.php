<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

            // Generate JWT token using PHP-OpenSourceSaver\JWTAuth
            $token = \JWTAuth::fromUser($user);
            
            // Generate refresh token
            $refreshToken = \JWTAuth::refresh($token);
            
            // Store refresh token in database
            DB::table('refresh_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'token' => $refreshToken,
                    'expires_at' => Carbon::now()->addDay()
                ]
            );

            return response()->json([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'message' => 'Token generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
