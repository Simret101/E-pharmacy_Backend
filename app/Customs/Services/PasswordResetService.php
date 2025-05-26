<?php

namespace App\Customs\Services;

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
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

        // Send reset notification
        try {
            Notification::send($user, new PasswordResetNotification($token));
            Log::info('Password reset notification sent successfully to: ' . $email);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset notification: ' . $e->getMessage());
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email'
        ]);

    } catch (\Exception $e) {
        Log::error('Password reset error: ' . $e->getMessage());
        return response()->json([
            'status' => 'failed',
            'message' => 'An error occurred while sending the password reset email. Please try again later.'
        ], 500);
    }
}
// app/Customs/Services/PasswordResetService.php

public function resetPassword($token, $password)
{
    try {
        // Find the token record
        $resetToken = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('created_at', '>', now()->subMinutes(60)) // Token expires after 60 minutes
            ->first();

        if (!$resetToken) {
            throw new \Exception('Invalid or expired reset token');
        }

        // Find the user
        $user = User::where('email', $resetToken->email)->first();

        if (!$user) {
            throw new \Exception('User not found');
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Delete the used token
        DB::table('password_reset_tokens')
            ->where('token', $token)
            ->delete();

        return [
            'status' => 'success',
            'message' => 'Password has been reset successfully'
        ];

    } catch (\Exception $e) {
        Log::error('Password reset error: ' . $e->getMessage());
        throw $e;
    }
}
}