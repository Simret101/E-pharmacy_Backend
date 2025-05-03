<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationService
{
    public function sendVerificationEmail(User $user)
    {
        try {
            // Check if user is already verified
            if ($user->email_verified_at) {
                Log::info('User already verified', ['user_id' => $user->id]);
                return false;
            }

            // Generate a new token
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);

            // Create or update the verification token
            EmailVerificationToken::updateOrCreate(
                ['email' => $user->email],
                [
                    'token' => $token,
                    'expired_at' => $expiresAt
                ]
            );

            // Send verification email
            Mail::to($user->email)->send(new EmailVerification($user, $token));

            Log::info('Verification email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    public function verifyEmail(string $token)
    {
        try {
            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('expired_at', '>', Carbon::now())
                ->first();

            if (!$verificationToken) {
                Log::warning('Invalid or expired verification token', ['token' => $token]);
                return false;
            }

            $user = User::where('email', $verificationToken->email)->first();

            if (!$user) {
                Log::warning('User not found for verification token', ['token' => $token]);
                return false;
            }

            // Mark email as verified
            $user->email_verified_at = Carbon::now();
            $user->save();

            // Delete the used token
            $verificationToken->delete();

            Log::info('Email verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to verify email', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);
            return false;
        }
    }
} 