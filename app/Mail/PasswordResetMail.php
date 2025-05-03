<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordResetMail extends Mailable
{
    public $resetUrl;

    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    public function build()
    {
        return $this->subject('Reset Your Password')
                    ->view('emails.password_reset')
                    ->with(['resetUrl' => $this->resetUrl]);
    }
}
