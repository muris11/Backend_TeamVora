<?php

namespace Tests\Feature\Api;

use App\Models\RecurringBill;
use App\Models\User;
use App\Services\RecurringBillService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RecurringBillTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $treasurer;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Notification::fake();

        $this->admin = User::factory()->create()->assignRole('Admin');
        $this->treasurer = User::factory()->create()->assignRole('Treasurer');
        $this->member = User::factory()->create()->assignRole('Member');
    }

    protected function validPayload(): array
    {
        return [
            'title' => 'Iuran Bulanan',
            'description' => 'Iuran kas rutin',
            'amount' => 100000,
            'frequency' => 'monthly',
            'due_day' => 10,
            'start_date' => now()->toDateString(),
            'assignee_ids' => [$this->member->id],
            'notify_days_before_due' => 3,
        ];
    }

    // --- Index ---

    public function test_user_can_list_recurring_bills(): void
    {
        RecurringBill::factory()->count(3)->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson('/api/recurring-bills')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_filters_by_status(): void
    {
        RecurringBill::factory()->create(['status' => 'active']);
        RecurringBill::factory()->create(['status' => 'paused']);

        $this->actingAs($this->admin)
            ->getJson('/api/recurring-bills?status=active')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // --- Store ---

    public function test_admin_can_create_recurring_bill(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/recurring-bills', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Iuran Bulanan');

        $this->assertDatabaseHas('recurring_bills', ['title' => 'Iuran Bulanan', 'status' => 'active']);
    }

    public function test_member_cannot_create(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/recurring-bills', $this->validPayload())
            ->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/recurring-bills', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'amount', 'frequency', 'start_date']);
    }

    public function test_store_rejects_invalid_frequency(): void
    {
        $payload = $this->validPayload();
        $payload['frequency'] = 'bogus';

        $this->actingAs($this->admin)
            ->postJson('/api/recurring-bills', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['frequency']);
    }

    // --- Show ---

    public function test_user_can_view_recurring_bill(): void
    {
        $bill = RecurringBill::factory()->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson("/api/recurring-bills/{$bill->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $bill->id);
    }

    // --- Update ---

    public function test_admin_can_update(): void
    {
        $bill = RecurringBill::factory()->create(['amount' => 50000]);

        $this->actingAs($this->admin)
            ->putJson("/api/recurring-bills/{$bill->id}", [
                ...$this->validPayload(),
                'amount' => 75000,
            ])
            ->assertOk();

        $this->assertEquals(75000, $bill->fresh()->amount);
    }

    public function test_member_cannot_update(): void
    {
        $bill = RecurringBill::factory()->create();

        $this->actingAs($this->member)
            ->putJson("/api/recurring-bills/{$bill->id}", $this->validPayload())
            ->assertForbidden();
    }

    // --- Destroy ---

    public function test_admin_can_destroy(): void
    {
        $bill = RecurringBill::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin)
            ->deleteJson("/api/recurring-bills/{$bill->id}")
            ->assertOk();

        $this->assertEquals('ended', $bill->fresh()->status);
    }

    public function test_member_cannot_destroy(): void
    {
        $bill = RecurringBill::factory()->create();

        $this->actingAs($this->member)
            ->deleteJson("/api/recurring-bills/{$bill->id}")
            ->assertForbidden();
    }

    // --- Toggle Active ---

    public function test_toggle_pauses_active_bill(): void
    {
        $bill = RecurringBill::factory()->create(['status' => 'active']);

        $this->actingAs($this->treasurer)
            ->postJson("/api/recurring-bills/{$bill->id}/toggle-active")
            ->assertOk();

        $this->assertEquals('paused', $bill->fresh()->status);
    }

    public function test_toggle_activates_paused_bill(): void
    {
        $bill = RecurringBill::factory()->create(['status' => 'paused']);

        $this->actingAs($this->treasurer)
            ->postJson("/api/recurring-bills/{$bill->id}/toggle-active")
            ->assertOk();

        $this->assertEquals('active', $bill->fresh()->status);
    }

    public function test_member_cannot_toggle(): void
    {
        $bill = RecurringBill::factory()->create();

        $this->actingAs($this->member)
            ->postJson("/api/recurring-bills/{$bill->id}/toggle-active")
            ->assertForbidden();
    }

    // --- Generate ---

    public function test_member_cannot_generate(): void
    {
        $bill = RecurringBill::factory()->create(['status' => 'active']);

        $this->actingAs($this->member)
            ->postJson("/api/recurring-bills/{$bill->id}/generate")
            ->assertForbidden();
    }

    public function test_generate_rejects_inactive_bill(): void
    {
        $bill = RecurringBill::factory()->create(['status' => 'paused']);

        $this->actingAs($this->admin)
            ->postJson("/api/recurring-bills/{$bill->id}/generate")
            ->assertUnprocessable();
    }

    // --- History ---

    public function test_user_can_view_history(): void
    {
        $bill = RecurringBill::factory()->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson("/api/recurring-bills/{$bill->id}/history")
            ->assertOk();
    }
}
