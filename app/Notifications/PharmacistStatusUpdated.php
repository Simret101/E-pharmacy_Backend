<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PharmacistStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $reason;

    public function __construct($status, $reason = null)
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'status' => $this->status,
            'reason' => $this->reason,
            'message' => 'Your registration has been ' . $this->status . '.'
        ];
    }
} 