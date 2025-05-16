<?php
use Illuminate\Support\Facades\Broadcast;

/**
 * Private chat channel for 1:1 conversations.
 * Only allows a user to listen to their own channel.
 */
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/**
 * Optional: General notification channel for authenticated users
 */
Broadcast::channel('notifications', function ($user) {
    return Auth::check(); // Or simply: return true;
});
