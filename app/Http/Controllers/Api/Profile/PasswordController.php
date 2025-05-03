<?php
namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Customs\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function changeUserPassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }

    public function sendResetLink(ForgotPasswordRequest $request)
    {
        try {
            Log::info('Starting password reset process for email: ' . $request->email);

            // Check if user exists
            $user = DB::table('users')->where('email', $request->email)->first();
            if (!$user) {
                Log::warning('Password reset attempt for non-existent email: ' . $request->email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No account found with this email address'
                ], 404);
            }

            Log::info('User found: ' . $user->id);

            // Check if there's a recent reset attempt
            $recentReset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('created_at', '>', now()->subMinutes(2))
                ->first();

            if ($recentReset) {
                Log::info('Password reset throttled for email: ' . $request->email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Please wait 2 minutes before requesting another password reset'
                ], 429);
            }

            // Clear any existing tokens for this email
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            Log::info('Cleared existing tokens for email: ' . $request->email);

            // Generate new token
            $token = Str::random(64);
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);

            Log::info('Generated new password reset token for email: ' . $request->email);

            // Send reset email directly
            try {
                Log::info('Attempting to send password reset email to: ' . $request->email);
                
                $resetUrl = url('/api/auth/password/reset/' . $token);
                Log::info('Reset URL generated: ' . $resetUrl);

                Mail::send('emails.password-reset', [
                    'resetUrl' => $resetUrl,
                    'user' => $user,
                    'expire' => config('auth.passwords.users.expire')
                ], function($message) use ($user) {
                    $message->to($user->email)
                           ->subject('Reset Your Password');
                });

                Log::info('Password reset email sent successfully to: ' . $request->email);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset link has been sent to your email'
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send password reset email: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while sending the password reset email. Please try again later.'
            ], 500);
        }
    }

    public function showResetForm($token)
    {
        try {
            $resetToken = DB::table('password_reset_tokens')
                ->where('token', $token)
                ->where('created_at', '>', now()->subMinutes(config('auth.passwords.users.expire')))
                ->first();

            if (!$resetToken) {
                return response()->view('auth.invalid-token', [], 422);
            }

            return view('auth.reset-password', [
                'token' => $token,
                'email' => $resetToken->email
            ]);

        } catch (\Exception $e) {
            Log::error('Token validation error: ' . $e->getMessage());
            return response()->view('auth.error', [
                'message' => 'An error occurred while validating the token'
            ], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            Log::info('Attempting password reset for email: ' . $request->email);

            // Verify token
            $resetToken = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->where('created_at', '>', now()->subMinutes(config('auth.passwords.users.expire')))
                ->first();

            if (!$resetToken) {
                Log::warning('Invalid or expired reset token for email: ' . $request->email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired reset token'
                ], 422);
            }

            // Update password
            $user = DB::table('users')->where('email', $request->email)->first();
            if (!$user) {
                Log::error('User not found for email: ' . $request->email);
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }

            DB::table('users')
                ->where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            // Delete used token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            Log::info('Password successfully reset for email: ' . $request->email);

            return response()->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while resetting your password. Please try again later.'
            ], 500);
        }
    }
}
