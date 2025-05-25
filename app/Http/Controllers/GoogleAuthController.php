<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GoogleAuthController extends Controller
{
    // Store intended role temporarily in redirect URL using state param
    public function redirect($role)
    {
        if (!in_array((int)$role, [0, 1, 2])) {
            return response()->json(['error' => 'Invalid role.'], 400);
        }

        // Pass role via `state` parameter
        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => json_encode(['role' => (int)$role])])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $state = json_decode($request->input('state'), true);
            $role = $state['role'] ?? 1; // default to patient

            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                    'is_role' => $role
                ]);
            }

            $token = JWTAuth::fromUser($user);
            $refreshToken = Hash::make(now());

            DB::table('refresh_tokens')->updateOrInsert(
                ['user_id' => $user->id],
                ['token' => $refreshToken, 'expires_at' => now()->addDay()]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Login via Google successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status,
                        'pharmacy_name' => $user->pharmacy_name ?? null,
                        'phone' => $user->phone ?? null,
                        'address' => $user->address ?? null,
                        'lat' => $user->lat ?? null,
                        'lng' => $user->lng ?? null,
                        'account_number' => $user->account_number ?? null,
                        'bank_name' => $user->bank_name ?? null,
                        'role' => $user->is_role
                    ],
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
