<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProofUploadedNotification extends Notification
{
    use Queueable;

    public $billItem;

    public $uploader;

    /**
     * Create a new notification instance.
     */
    public function __construct($billItem, $uploader)
    {
        $this->billItem = $billItem;
        $this->uploader = $uploader;
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
            'type' => 'proof_uploaded',
            'title' => 'Bukti Pembayaran Diunggah',
            'message' => $this->uploader->name.' mengunggah bukti bayar untuk: '.$this->billItem->splitBill->title,
            'url' => '/finance/bills',
        ];
    }
}
