<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewPharmacistRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pharmacist;

    public function __construct(User $pharmacist)
    {
        $this->pharmacist = $pharmacist;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Pharmacist Registration - Action Required')
            ->greeting('Hello Admin,')
            ->line('A new pharmacist has registered on the platform and requires your review.')
            ->line('Pharmacist Details:')
            ->line('Name: ' . $this->pharmacist->name)
            ->line('Email: ' . $this->pharmacist->email)
            ->line('Pharmacy Name: ' . $this->pharmacist->pharmacy_name)
            ->line('Phone: ' . $this->pharmacist->phone)
            ->line('TIN Number: ' . $this->pharmacist->tin_number)
            ->line('Bank Details:')
            ->line('Account Number: ' . $this->pharmacist->account_number)
            ->line('Bank Name: ' . $this->pharmacist->bank_name)
            ->line('Please review their documents and take appropriate action.')
            ->action('Review Registration', url('/admin/pharmacists'))
            ->line('Thank you for your attention to this matter.')
            ->salutation('Best regards,')
            ->line('EPharmacy System');
    }

    public function toArray($notifiable)
    {
        return [
            'pharmacist_id' => $this->pharmacist->id,
            'pharmacist_name' => $this->pharmacist->name,
            'pharmacist_email' => $this->pharmacist->email,
            'pharmacy_name' => $this->pharmacist->pharmacy_name,
            'message' => 'New pharmacist registration requires review',
            'type' => 'pharmacist_registration',
            'timestamp' => now()->toDateTimeString()
        ];
    }
} 