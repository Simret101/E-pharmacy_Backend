<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Customs\Services\CloudinaryService;

class AdminPharmacistRegistration extends Mailable
{
    use Queueable, SerializesModels;

    public $pharmacist;
    public $tinVerificationUrl;
    public $licenseImageUrl;
    public $tinImageUrl;

    public function __construct(User $pharmacist)
    {
        $this->pharmacist = $pharmacist;
        $this->tinVerificationUrl = 'https://etrade.gov.et/business-license-checker?tin=' . $pharmacist->tin_number;
        
        // Get Cloudinary URLs
        $cloudinaryService = app(CloudinaryService::class);
        $this->licenseImageUrl = $cloudinaryService->getImageUrl($pharmacist->license_image);
        $this->tinImageUrl = $cloudinaryService->getImageUrl($pharmacist->tin_image);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Pharmacist Registration - TIN Verification Required',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.pharmacist-registration',
            with: [
                'pharmacist' => $this->pharmacist,
                'tinVerificationUrl' => $this->tinVerificationUrl,
                'licenseImageUrl' => $this->licenseImageUrl,
                'tinImageUrl' => $this->tinImageUrl,
            ],
        );
    }
} 