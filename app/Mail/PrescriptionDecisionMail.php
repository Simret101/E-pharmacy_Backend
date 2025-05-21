<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Order;
use App\Customs\Services\CloudinaryService;

class PrescriptionDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $order;
    public $status;
    public $message;
    public $prescriptionImageUrl;

    public function __construct(User $user, Order $order, $status, $message, $prescriptionImageUrl)
    {
        $this->user = $user;
        $this->order = $order;
        $this->status = $status;
        $this->message = $message;
        $this->prescriptionImageUrl = $prescriptionImageUrl;
    }

    public function build()
    {
        return $this->subject('Prescription Status Update')
                    ->view('emails.admin.prescription-decision');
    }
}