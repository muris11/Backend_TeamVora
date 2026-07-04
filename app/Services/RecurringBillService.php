<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RecurringBill;
use App\Models\RecurringBillGeneration;
use App\Models\SplitBill;
use App\Models\User;
use App\Notifications\RecurringBillGeneratedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringBillService
{
    public function generateCycle(RecurringBill $rb, bool $dryRun = false): ?SplitBill
    {
        $dueDate = $this->computeDueDate($rb);

        if ($dryRun) {
            Log::info("[DRY-RUN] Would generate for {$rb->title}, due: {$dueDate}, assignees: ".count($rb->assignee_ids ?? []));

            return null;
        }

        $assigneeIds = $rb->assignee_ids ?? [];
        $count = count($assigneeIds);
        if ($count === 0) {
            Log::warning("RecurringBill {$rb->id} has no assignees, skipping.");

            return null;
        }

        $perPerson = round($rb->amount / $count, 2);
        $remainder = round($rb->amount - ($perPerson * $count), 2);

        return DB::transaction(function () use ($rb, $dueDate, $assigneeIds, $perPerson, $remainder) {
            $bill = SplitBill::create([
                'creator_id' => $rb->creator_id,
                'title' => $rb->title,
                'description' => $rb->description,
                'total_amount' => $rb->amount,
                'due_date' => $dueDate,
                'status' => 'active',
                'parent_recurring_bill_id' => $rb->id,
            ]);

            foreach ($assigneeIds as $i => $userId) {
                $amount = $i === 0 ? round($perPerson + $remainder, 2) : $perPerson;

                $item = $bill->items()->create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'status' => 'unpaid',
                ]);

                $user = User::find($userId);
                if ($user) {
                    $user->notify(new RecurringBillGeneratedNotification(
                        recurringBillId: $rb->id,
                        splitBillId: $bill->id,
                        title: $bill->title,
                        amount: $amount,
                        dueDate: $dueDate,
                    ));
                }
            }

            RecurringBillGeneration::create([
                'recurring_bill_id' => $rb->id,
                'split_bill_id' => $bill->id,
                'generated_at' => today(),
            ]);

            return $bill;
        });
    }

    public function computeDueDate(RecurringBill $rb): string
    {
        $today = today();

        return match ($rb->frequency) {
            'daily' => (string) $today->copy()->addDay()->toDateString(),
            'weekly' => (string) $today->copy()->addWeek()->toDateString(),
            'monthly' => $this->computeMonthlyDue($rb, $today),
            'quarterly' => $this->computeMonthlyDue($rb, $today, 3),
            'yearly' => $this->computeMonthlyDue($rb, $today, 12),
            'custom_days' => (string) $today->copy()->addDays($rb->interval_days ?? 1)->toDateString(),
            default => (string) $today->copy()->addWeek()->toDateString(),
        };
    }

    public function computeNextGeneration(RecurringBill $rb): string
    {
        $lastGen = $rb->last_generated_at ? $rb->last_generated_at->copy() : today();

        return match ($rb->frequency) {
            'daily' => (string) $lastGen->addDay()->toDateString(),
            'weekly' => (string) $lastGen->addWeek()->toDateString(),
            'monthly' => $this->computeNextMonthly($rb, $lastGen),
            'quarterly' => $this->computeNextMonthly($rb, $lastGen, 3),
            'yearly' => $this->computeNextMonthly($rb, $lastGen, 12),
            'custom_days' => (string) $lastGen->addDays($rb->interval_days ?? 1)->toDateString(),
            default => (string) $lastGen->addWeek()->toDateString(),
        };
    }

    private function computeMonthlyDue(RecurringBill $rb, Carbon $anchor, int $months = 1): string
    {
        $date = $anchor->copy()->addMonthsNoOverflow($months);
        if ($rb->due_day) {
            $day = min($rb->due_day, $date->daysInMonth);
            $date->setDay($day);
        }

        return (string) $date->toDateString();
    }

    private function computeNextMonthly(RecurringBill $rb, Carbon $lastGen, int $months = 1): string
    {
        $date = $lastGen->copy()->addMonthsNoOverflow($months);
        if ($rb->due_day) {
            $day = min($rb->due_day, $date->daysInMonth);
            $date->setDay($day);
        }

        return (string) $date->toDateString();
    }
}
