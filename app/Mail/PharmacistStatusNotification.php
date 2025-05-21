<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PharmacistStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $status;
    public $loginUrl;

    public function __construct(User $user, string $status)
    {
        $this->user = $user;
        $this->status = $status;
        $this->loginUrl = 'https://e-pharacy.vercel.app/login';
    }

    public function build()
    {
        return $this->subject($this->getStatusSubject())
                    ->view('emails.pharmacist-status-notification')
                    ->with([
                        'user' => $this->user,
                        'status' => $this->status,
                        'loginUrl' => $this->loginUrl,
                        'statusMessage' => $this->getStatusMessage(),
                    ]);
    }

    private function getStatusSubject(): string
    {
        return match($this->status) {
            'pending' => 'Pharmacist Registration Pending',
            'approved' => 'Pharmacist Registration Approved',
            'rejected' => 'Pharmacist Registration Rejected',
            default => 'Pharmacist Registration Status Update',
        };
    }

    private function getStatusMessage(): string
    {
        return match($this->status) {
            'pending' => 'Your pharmacist registration is currently under review. Please wait for approval.',
            'approved' => 'Your pharmacist registration has been approved. You can now log in to your account.',
            'rejected' => 'Your pharmacist registration has been rejected. Please contact support for more information.',
            default => 'Your pharmacist registration status has been updated.',
        };
    }
}
