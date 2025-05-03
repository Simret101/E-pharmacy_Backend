<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class AdminPharmacistRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pharmacist;
    protected $licenseImage;
    protected $tinImage;
    protected $verificationToken;

    public function __construct(User $pharmacist, $licenseImage, $tinImage, $verificationToken)
    {
        $this->pharmacist = $pharmacist;
        $this->licenseImage = $licenseImage;
        $this->tinImage = $tinImage;
        $this->verificationToken = $verificationToken;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = route('verification.notice', ['token' => $this->verificationToken]);
        $approveUrl = route('admin.pharmacist.action', [
            'id' => $this->pharmacist->id,
            'status' => 'approved',
            'reason' => 'Documents verified'
        ]);
        $rejectUrl = route('admin.pharmacist.action', [
            'id' => $this->pharmacist->id,
            'status' => 'rejected',
            'reason' => 'Documents not verified'
        ]);

        return (new MailMessage)
            ->subject('New Pharmacist Registration - Action Required')
            ->greeting('Hello Admin,')
            ->line('A new pharmacist has registered and requires your approval.')
            ->line('Pharmacist Details:')
            ->line('Name: ' . $this->pharmacist->name)
            ->line('Email: ' . $this->pharmacist->email)
            ->line('License Number: ' . $this->pharmacist->license_number)
            ->line('Please review the documents and take appropriate action:')
            ->line('To view the documents and take action, please visit the admin dashboard.')
            ->action('Go to Admin Dashboard', route('admin.dashboard'))
            ->line('Thank you for your attention to this matter.');
    }
} 