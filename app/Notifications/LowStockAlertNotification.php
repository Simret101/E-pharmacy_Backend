<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Drug;

class LowStockAlertNotification extends Notification
{
    use Queueable;

    public $drug;

    public function __construct(Drug $drug)
    {
        $this->drug = $drug;
    }

    public function via($notifiable)
    {
        return ['database']; // You can also add 'mail' if you want email too
    }

    public function toDatabase($notifiable)
    {
        return [
            'drug_id' => $this->drug->id,
            'drug_name' => $this->drug->name,
            'stock' => $this->drug->stock,
            'message' => 'The drug ' . $this->drug->name . ' is running low in stock.'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'drug_id' => $this->drug->id,
            'drug_name' => $this->drug->name,
            'stock' => $this->drug->stock,
            'message' => 'The drug ' . $this->drug->name . ' is running low in stock.'
        ];
    }
}