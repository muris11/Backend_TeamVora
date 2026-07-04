<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BillItem;
use App\Notifications\BillDueReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:remind-bill-items')]
#[Description('Send reminders for bill items due within the configured notification window')]
class RemindBillItems extends Command
{
    public function handle()
    {
        $reminded = 0;

        BillItem::query()
            ->where('status', 'unpaid')
            ->whereNull('reminder_sent_at')
            ->whereHas('bill', function ($q) {
                $q->whereNotNull('due_date')
                    ->where('due_date', '<=', now()->addDays(3))
                    ->where('due_date', '>=', now());
            })
            ->chunk(50, function ($items) use (&$reminded) {
                foreach ($items as $item) {
                    try {
                        $item->user->notify(new BillDueReminderNotification($item));
                        $item->update(['reminder_sent_at' => now()]);
                        $reminded++;
                    } catch (\Exception $e) {
                        Log::warning(
                            "Gagal kirim reminder bill item {$item->id}: {$e->getMessage()}"
                        );
                    }
                }
            });

        $this->info("Mengirim {$reminded} pengingat tagihan.");

        return 0;
    }
}
