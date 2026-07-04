<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecurringBill;
use App\Services\RecurringBillService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-recurring-bills')]
#[Description('Generate bill cycles for recurring bills that are due')]
class GenerateRecurringBills extends Command
{
    public function handle(RecurringBillService $service)
    {
        $bills = RecurringBill::dueForGeneration()->get();

        if ($bills->isEmpty()) {
            $this->info('Tidak ada tagihan berulang yang perlu dibuat.');

            return 0;
        }

        $generated = 0;

        foreach ($bills as $bill) {
            try {
                $result = $service->generateCycle($bill);
                if ($result) {
                    $generated++;
                }
            } catch (\Exception $e) {
                $this->error("Gagal generate {$bill->title}: {$e->getMessage()}");
                $bill->update(['last_error_at' => now()]);
            }
        }

        $this->info("Berhasil membuat {$generated} tagihan berulang.");

        return 0;
    }
}
