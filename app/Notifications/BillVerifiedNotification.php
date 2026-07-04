<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillVerifiedNotification extends Notification
{
    use Queueable;

    public $billItem;

    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($billItem, $status)
    {
        $this->billItem = $billItem;
        $this->status = $status;
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
        $isPaid = $this->status === 'paid';

        return [
            'type' => 'bill_verified',
            'title' => $isPaid ? 'Pembayaran Diterima' : 'Pembayaran Ditolak',
            'message' => 'Bukti bayar untuk '.$this->billItem->splitBill->title.($isPaid ? ' telah diverifikasi.' : ' ditolak.'),
            'url' => '/finance/bills',
        ];
    }
}
