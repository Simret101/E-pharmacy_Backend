<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $status;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function build()
    {
        $subject = $this->status === 'approved' ? 'Order Approved' : 'Order Rejected';
        
        return $this->subject($subject)
                    ->view('emails.order-status-notification')
                    ->with([
                        'order' => $this->order,
                        'status' => $this->status
                    ]);
    }
}
