<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();
        
        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email already verified'
            ], 400);
        }

        // Delete any existing tokens
        EmailVerificationToken::where('email', $user->email)->delete();

        // Create new token
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours(24);

        EmailVerificationToken::create([
            'email' => $user->email,
            'token' => $token,
            'expired_at' => $expiresAt
        ]);

        // Send verification email
        $user->notify(new VerifyEmail($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Verification email sent successfully'
        ]);
    }

    public function verifyEmail($token)
    {
        $verificationToken = EmailVerificationToken::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$verificationToken) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid or expired verification token'
            ], 400);
        }

        DB::transaction(function () use ($verificationToken) {
            // Update user's email verification status
            User::where('email', $verificationToken->email)
                ->update(['email_verified_at' => Carbon::now()]);

            // Delete the used token
            $verificationToken->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully'
        ]);
    }
} 