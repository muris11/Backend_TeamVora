<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillCreatedNotification extends Notification
{
    use Queueable;

    public $bill;

    /**
     * Create a new notification instance.
     */
    public function __construct($bill)
    {
        $this->bill = $bill;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bill_created',
            'title' => 'Tagihan Baru',
            'message' => 'Anda mendapat tagihan baru: '.$this->bill->title,
            'url' => '/finance/bills',
        ];
    }
}
