<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\BillItem;
use App\Models\RecurringBill;
use App\Models\SplitBill;
use App\Models\User;
use App\Services\RecurringBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RecurringBillServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RecurringBillService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(RecurringBillService::class);
    }

    // --- computeDueDate ---

    public function test_compute_due_date_daily(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'daily']);

        $due = $this->service->computeDueDate($bill);

        $this->assertEquals(today()->addDay()->toDateString(), $due);
    }

    public function test_compute_due_date_weekly(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'weekly']);

        $due = $this->service->computeDueDate($bill);

        $this->assertEquals(today()->addWeek()->toDateString(), $due);
    }

    public function test_compute_due_date_monthly_with_due_day(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'monthly', 'due_day' => 15]);

        $due = $this->service->computeDueDate($bill);

        $expected = today()->addMonthNoOverflow()->setDay(15)->toDateString();
        $this->assertEquals($expected, $due);
    }

    public function test_compute_due_date_quarterly(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'quarterly', 'due_day' => 1]);

        $due = $this->service->computeDueDate($bill);

        $expected = today()->addMonthsNoOverflow(3)->setDay(1)->toDateString();
        $this->assertEquals($expected, $due);
    }

    public function test_compute_due_date_yearly(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'yearly', 'due_day' => 5]);

        $due = $this->service->computeDueDate($bill);

        $expected = today()->addMonthsNoOverflow(12)->setDay(5)->toDateString();
        $this->assertEquals($expected, $due);
    }

    public function test_compute_due_date_custom_days(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'custom_days', 'interval_days' => 14]);

        $due = $this->service->computeDueDate($bill);

        $this->assertEquals(today()->addDays(14)->toDateString(), $due);
    }

    public function test_compute_due_date_defaults_to_weekly(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'bogus']);

        $due = $this->service->computeDueDate($bill);

        $this->assertEquals(today()->addWeek()->toDateString(), $due);
    }

    // --- computeNextGeneration ---

    public function test_compute_next_generation_daily(): void
    {
        $bill = RecurringBill::factory()->make(['frequency' => 'daily']);

        $next = $this->service->computeNextGeneration($bill);

        $this->assertEquals(today()->addDay()->toDateString(), $next);
    }

    public function test_compute_next_generation_uses_last_generated_at(): void
    {
        $lastGen = today()->subDays(5);
        $bill = RecurringBill::factory()->make([
            'frequency' => 'daily',
            'last_generated_at' => $lastGen,
        ]);

        $next = $this->service->computeNextGeneration($bill);

        $this->assertEquals($lastGen->copy()->addDay()->toDateString(), $next);
    }

    public function test_compute_next_generation_weekly(): void
    {
        $lastGen = today()->subDays(10);
        $bill = RecurringBill::factory()->make([
            'frequency' => 'weekly',
            'last_generated_at' => $lastGen,
        ]);

        $next = $this->service->computeNextGeneration($bill);

        $this->assertEquals($lastGen->copy()->addWeek()->toDateString(), $next);
    }

    public function test_compute_next_generation_monthly(): void
    {
        $bill = RecurringBill::factory()->make([
            'frequency' => 'monthly',
            'due_day' => 15,
            'last_generated_at' => today()->subMonth(),
        ]);

        $next = $this->service->computeNextGeneration($bill);

        $expected = today()->subMonth()->addMonthNoOverflow()->setDay(15)->toDateString();
        $this->assertEquals($expected, $next);
    }

    // --- generateCycle ---

    public function test_generate_cycle_creates_split_bill(): void
    {
        $assignee = User::factory()->create();
        $creator = User::factory()->create();
        $bill = RecurringBill::factory()->create([
            'creator_id' => $creator->id,
            'amount' => 100_000,
            'frequency' => 'monthly',
            'due_day' => 15,
            'assignee_ids' => [$assignee->id],
            'status' => 'active',
        ]);

        $result = $this->service->generateCycle($bill);

        $this->assertNotNull($result);
        $this->assertInstanceOf(SplitBill::class, $result);
        $this->assertEquals($bill->title, $result->title);
        $this->assertEquals(100_000, (float) $result->total_amount);
    }

    public function test_generate_cycle_creates_bill_items_and_logs_generation(): void
    {
        Notification::fake();

        $assignees = User::factory()->count(2)->create();
        $creator = User::factory()->create();
        $assigneeIds = $assignees->pluck('id')->toArray();
        $bill = RecurringBill::factory()->create([
            'creator_id' => $creator->id,
            'amount' => 100_000,
            'frequency' => 'monthly',
            'due_day' => 15,
            'assignee_ids' => $assigneeIds,
            'status' => 'active',
        ]);

        $result = $this->service->generateCycle($bill);

        $this->assertNotNull($result);
        $this->assertCount(2, $result->items);

        $totalFromItems = (float) $result->items->sum('amount');
        $this->assertEquals(100_000, $totalFromItems);

        $this->assertDatabaseHas('recurring_bill_generations', [
            'recurring_bill_id' => $bill->id,
            'split_bill_id' => $result->id,
        ]);

        Notification::assertCount(2);
    }

    public function test_generate_cycle_returns_null_for_no_assignees(): void
    {
        $bill = RecurringBill::factory()->create([
            'amount' => 100_000,
            'frequency' => 'monthly',
            'assignee_ids' => [],
            'status' => 'active',
        ]);

        $result = $this->service->generateCycle($bill);

        $this->assertNull($result);
    }

    public function test_generate_cycle_child_amounts_sum_to_parent(): void
    {
        $assignees = User::factory()->count(3)->create();
        $creator = User::factory()->create();
        $bill = RecurringBill::factory()->create([
            'creator_id' => $creator->id,
            'amount' => 100_000,
            'frequency' => 'monthly',
            'due_day' => 15,
            'assignee_ids' => $assignees->pluck('id')->toArray(),
            'status' => 'active',
        ]);

        $result = $this->service->generateCycle($bill);

        $total = (float) $result->items->sum('amount');
        $this->assertEquals(100_000, $total);
    }

    public function test_dry_run_returns_null(): void
    {
        $bill = RecurringBill::factory()->create([
            'amount' => 100_000,
            'frequency' => 'monthly',
            'assignee_ids' => [User::factory()->create()->id],
            'status' => 'active',
        ]);

        $result = $this->service->generateCycle($bill, dryRun: true);

        $this->assertNull($result);
    }

    // --- Scope: dueForGeneration ---

    public function test_due_for_generation_scope(): void
    {
        RecurringBill::factory()->create([
            'status' => 'active',
            'next_generation_at' => today()->subDay(),
        ]);
        RecurringBill::factory()->create([
            'status' => 'active',
            'next_generation_at' => today()->addDay(),
        ]);
        RecurringBill::factory()->create([
            'status' => 'paused',
            'next_generation_at' => today()->subDay(),
        ]);

        $due = RecurringBill::dueForGeneration()->get();

        $this->assertCount(1, $due);
    }
}
