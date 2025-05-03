<?php

use Illuminate\Support\Facades\Broadcast;

// Private chat channel for individual users
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Channel for admin to receive messages from all users
Broadcast::channel('admin.chat', function ($user) {
    return $user->is_role === 0; // Only admin can access this channel
});

// Channel for pharmacists to receive messages from admin and patients
Broadcast::channel('pharmacist.chat.{pharmacistId}', function ($user, $pharmacistId) {
    return $user->is_role === 2 && (int) $user->id === (int) $pharmacistId;
});

// Channel for patients to receive messages from admin and pharmacists
Broadcast::channel('patient.chat.{patientId}', function ($user, $patientId) {
    return $user->is_role === 1 && (int) $user->id === (int) $patientId;
}); 