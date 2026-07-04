<?php

namespace Tests\Feature\Api;

use App\Models\BillItem;
use App\Models\SplitBill;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SplitBillTest extends TestCase
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
        $this->admin = User::factory()->create()->assignRole('super_admin');
        $this->treasurer = User::factory()->create()->assignRole('team_leader');
        $this->member = User::factory()->create()->assignRole('member');
    }

    public function test_user_can_list_split_bills(): void
    {
        SplitBill::factory()->count(2)->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->getJson('/api/split-bills')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_create_split_bill(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/split-bills', [
                'title' => 'Makan bersama',
                'total_amount' => 200000,
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [
                    ['user_id' => $this->member->id, 'amount' => 100000],
                    ['user_id' => $this->admin->id, 'amount' => 100000],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Makan bersama');

        $this->assertDatabaseHas('split_bills', ['title' => 'Makan bersama']);
        $this->assertDatabaseCount('bill_items', 2);
    }

    public function test_member_cannot_create_split_bill(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/split-bills', [
                'title' => 'Test',
                'total_amount' => 100000,
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [['user_id' => $this->member->id, 'amount' => 100000]],
            ])
            ->assertForbidden();
    }

    public function test_store_rejects_mismatched_total(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/split-bills', [
                'title' => 'Test',
                'total_amount' => 200000,
                'due_date' => now()->addDays(7)->toDateString(),
                'items' => [
                    ['user_id' => $this->member->id, 'amount' => 100000],
                ],
            ])
            ->assertUnprocessable();
    }

    public function test_user_can_view_split_bill(): void
    {
        $bill = SplitBill::factory()->create(['creator_id' => $this->admin->id]);
        BillItem::factory()->create(['split_bill_id' => $bill->id, 'user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->getJson("/api/split-bills/{$bill->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $bill->id);
    }

    public function test_member_can_pay_bill_item(): void
    {
        $creator = User::factory()->create();
        $bill = SplitBill::factory()->create(['creator_id' => $creator->id]);
        $item = BillItem::factory()->create([
            'split_bill_id' => $bill->id,
            'user_id' => $this->member->id,
            'status' => 'unpaid',
        ]);

        \Illuminate\Support\Facades\Storage::fake('s3');

        $this->actingAs($this->member)
            ->postJson("/api/bill-items/{$item->id}/pay", [
                'proof_file' => \Illuminate\Http\UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertOk();

        $this->assertEquals('pending_verification', $item->fresh()->status);
    }

    public function test_treasurer_can_verify_bill_item(): void
    {
        $creator = User::factory()->create();
        $bill = SplitBill::factory()->create(['creator_id' => $creator->id]);
        $item = BillItem::factory()->create([
            'split_bill_id' => $bill->id,
            'user_id' => $this->member->id,
            'status' => 'pending_verification',
            'proof_path' => 'http://example.com/proof.jpg',
        ]);

        $this->actingAs($this->treasurer)
            ->putJson("/api/bill-items/{$item->id}/verify", ['status' => 'paid'])
            ->assertOk();

        $this->assertEquals('paid', $item->fresh()->status);
    }

    public function test_member_cannot_verify(): void
    {
        $item = BillItem::factory()->create(['status' => 'pending_verification']);

        $this->actingAs($this->member)
            ->putJson("/api/bill-items/{$item->id}/verify", ['status' => 'paid'])
            ->assertForbidden();
    }
}
