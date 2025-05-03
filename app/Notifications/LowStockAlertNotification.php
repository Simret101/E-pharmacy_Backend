<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Drug;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $drug;

    public function __construct(Drug $drug)
    {
        $this->drug = $drug;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->drug->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a low stock alert for one of your drugs:')
            ->line('Drug: ' . $this->drug->name)
            ->line('Current Stock: ' . $this->drug->stock)
            ->line('Please restock as soon as possible to avoid running out.')
            ->action('Manage Inventory', url('/pharmacist/drugs'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'drug_id' => $this->drug->id,
            'drug_name' => $this->drug->name,
            'stock' => $this->drug->stock,
            'message' => 'Low stock alert for ' . $this->drug->name,
            'type' => 'low_stock_alert'
        ];
    }
} 