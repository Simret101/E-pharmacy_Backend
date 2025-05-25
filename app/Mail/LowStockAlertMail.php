<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Drug;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $drug;

    public function __construct(Drug $drug)
    {
        $this->drug = $drug;
    }

    public function build()
    {
        return $this->subject('Low Stock Alert: ' . $this->drug->name)
                    ->view('emails.low-stock-alert');
    }
}