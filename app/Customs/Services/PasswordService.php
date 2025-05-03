<?php

namespace App\Customs\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use App\Mail\PasswordResetMail;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;

class PasswordService

{
    
    private function validateCurrentPassword($current_password)
    {
        if (!password_verify($current_password, Auth::user()->password)) {
            response()->json([
                'status' => 'failed',
                'message' => 'Password did not match the current password'
            ])->send();
            exit;
        }
    }

    public function changePassword($data): JsonResponse
    {
        $this->validateCurrentPassword($data['current_password']);
        $user = Auth::user();

        if ($user instanceof User) {
            $updatePassword = $user->update([
                'password' => Hash::make($data['password'])
            ]);

            if ($updatePassword) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'An error occurred while updating password'
                ]);
            }
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'User not authenticated'
        ], 401);
    }


    
    public function sendResetLink(string $email): JsonResponse
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found.'
            ], 404);
        }
    
      
        $token = Str::random(60);
    
   
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );
    
       
        $newPassword = Str::random(8); 
        $hashedPassword = Hash::make($newPassword);
    
    
        $user->forceFill(['password' => $hashedPassword])->save();
    
        $resetUrl = URL::to('/password/reset/' . $token);
    
       
        Mail::send('emails.password_reset', ['password' => $newPassword, 'resetUrl' => $resetUrl], function ($message) use ($email) {
            $message->to($email)->subject('Your Password Reset');
        });
    
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset email sent successfully. Check your email for the new password.'
        ]);
    }
    
  
   


   
}
