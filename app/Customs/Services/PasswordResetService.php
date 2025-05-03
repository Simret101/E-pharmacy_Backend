<?php

namespace App\Customs\Services;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    public function sendResetLink($email)
    {
        try {
            Log::info('Starting password reset process for email: ' . $email);

            // Check if user exists
            $user = User::where('email', $email)->first();
            if (!$user) {
                Log::warning('Password reset attempt for non-existent email: ' . $email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No account found with this email address'
                ], 404);
            }

            Log::info('User found: ' . $user->id);

            // Check if there's a recent reset attempt
            $recentReset = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('created_at', '>', now()->subMinutes(2))
                ->first();

            if ($recentReset) {
                Log::info('Password reset throttled for email: ' . $email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Please wait 2 minutes before requesting another password reset'
                ], 429);
            }

            // Clear any existing tokens for this email
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            Log::info('Cleared existing tokens for email: ' . $email);

            // Generate new token
            $token = Str::random(64);
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => now()
            ]);

            Log::info('Generated new password reset token for email: ' . $email);

            // Test mail configuration first
            try {
                Mail::raw('Test email configuration', function($message) use ($email) {
                    $message->to($email)
                           ->subject('Test Email Configuration');
                });
                Log::info('Test email sent successfully to: ' . $email);
            } catch (\Exception $e) {
                Log::error('Test email failed: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }

            // Send reset email
            try {
                Log::info('Attempting to send password reset notification to: ' . $email);
                $user->notify(new ResetPassword($token));
                Log::info('Password reset notification sent successfully to: ' . $email);
            } catch (\Exception $e) {
                Log::error('Failed to send password reset notification: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link has been sent to your email'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while sending the password reset email. Please try again later.'
            ], 500);
        }
    }

    public function resetPassword($email, $token, $password)
    {
        try {
            Log::info('Attempting password reset for email: ' . $email);

            // Verify token
            $resetToken = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('token', $token)
                ->where('created_at', '>', now()->subMinutes(config('auth.passwords.users.expire')))
                ->first();

            if (!$resetToken) {
                Log::warning('Invalid or expired reset token for email: ' . $email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired reset token'
                ], 422);
            }

            // Update password
            $user = User::where('email', $email)->first();
            $user->password = bcrypt($password);
            $user->save();

            // Delete used token
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('token', $token)
                ->delete();

            Log::info('Password successfully reset for email: ' . $email);

            return response()->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while resetting your password. Please try again later.'
            ], 500);
        }
    }

    public function validateToken($token)
    {
        try {
            $resetToken = DB::table('password_reset_tokens')
                ->where('token', $token)
                ->where('created_at', '>', now()->subMinutes(config('auth.passwords.users.expire')))
                ->first();

            if (!$resetToken) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired reset token'
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'email' => $resetToken->email,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            Log::error('Token validation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while validating the token'
            ], 500);
        }
    }
} 