<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RecurringBillGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $recurringBillId,
        public string $splitBillId,
        public string $title,
        public float $amount,
        public string $dueDate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'recurring_bill_generated',
            'message' => "Tagihan berulang '{$this->title}' telah dibuat. Besaran: Rp ".number_format($this->amount, 0, ',', '.'),
            'recurring_bill_id' => $this->recurringBillId,
            'split_bill_id' => $this->splitBillId,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'url' => route('finance.bills.show', $this->splitBillId),
        ];
    }
}
