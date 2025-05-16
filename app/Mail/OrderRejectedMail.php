<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $reason;

    public function __construct(Order $order, string $reason = null)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order Has Been Rejected - Order #' . $this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.order-rejected',
            with: [
                'order' => $this->order,
                'reason' => $this->reason,
            ],
        );
    }
}
