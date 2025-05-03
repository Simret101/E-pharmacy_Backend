<?php

namespace App\Services;

use App\Models\User;
use App\Mail\AdminPharmacistRegistration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminEmailService
{
    public function sendNewPharmacistNotification(User $pharmacist)
    {
        try {
            // Get all admin users
            $admins = User::where('is_role', 0)->get();
            
            if ($admins->isEmpty()) {
                Log::warning('No admin users found to send pharmacist registration notification');
                return false;
            }

            // Send email to each admin
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new AdminPharmacistRegistration($pharmacist));
            }

            Log::info('Pharmacist registration notification sent to admins', [
                'pharmacist_id' => $pharmacist->id,
                'admin_count' => $admins->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send pharmacist registration notification', [
                'error' => $e->getMessage(),
                'pharmacist_id' => $pharmacist->id
            ]);
            return false;
        }
    }
} 