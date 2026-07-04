<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\BillItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public BillItem $billItem,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $bill = $this->billItem->bill;

        return [
            'type' => 'bill_due_reminder',
            'message' => "Tagihan '{$bill->title}' sebesar Rp ".number_format($this->billItem->amount, 0, ',', '.').' akan jatuh tempo pada '.$bill->due_date->format('d/m/Y').'.',
            'bill_item_id' => $this->billItem->id,
            'split_bill_id' => $bill->id,
            'amount' => $this->billItem->amount,
            'due_date' => $bill->due_date->format('Y-m-d'),
            'url' => '/api/split-bills/' . $bill->id,
        ];
    }
}
