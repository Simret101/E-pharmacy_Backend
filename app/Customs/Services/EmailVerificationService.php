<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use Illuminate\Support\Str;
use App\Notifications\EmailVerificationNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class EmailVerificationService
{
    public function sendVerificationLink(object $user): void
    {
        $verificationLink = $this->generateVerificationLink($user->email);
        
        if ($verificationLink) {
            Notification::send($user, new EmailVerificationNotification($verificationLink));
        }
    }

    public function resendLink($email)
    {
        $user = User::where("email", $email)->first();
        if ($user) {
            $this->sendVerificationLink($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Verification link sent successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ]);
        }
    }

    public function verifyEmail(string $email, string $token)
    {
        try {
            DB::beginTransaction();

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }

            $verificationToken = EmailVerificationToken::where('email', $email)
                ->where('token', $token)
                ->where('expired_at', '>', now())
                ->first();

            if (!$verificationToken) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired verification token'
                ], 400);
            }

            $user->email_verified_at = now();
            $user->save();

            $verificationToken->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Error verifying email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateVerificationLink(string $email): ?string
    {
        try {
            // Delete any existing tokens for this email
            EmailVerificationToken::where('email', $email)->delete();

            // Generate new token
            $token = Str::random(64);
            $expiresAt = now()->addHours(24);

            // Create new verification token
            EmailVerificationToken::create([
                'email' => $email,
                'token' => $token,
                'expired_at' => $expiresAt
            ]);

            // Generate verification URL
            return url('/api/verify-email/' . $token);
        } catch (\Exception $e) {
            \Log::error('Error generating verification link: ' . $e->getMessage());
            return null;
        }
    }
}