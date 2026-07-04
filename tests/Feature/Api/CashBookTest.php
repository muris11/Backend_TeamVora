<?php

namespace Tests\Feature\Api;

use App\Models\CashBook;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashBookTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $treasurer;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create()->assignRole('super_admin');
        $this->treasurer = User::factory()->create()->assignRole('team_leader');
        $this->member = User::factory()->create()->assignRole('member');
    }

    // --- Index ---

    public function test_user_can_list_cash_books(): void
    {
        CashBook::factory()->count(3)->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson('/api/cash-books')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_includes_summary(): void
    {
        CashBook::factory()->create(['type' => 'in', 'amount' => 200000, 'created_by' => $this->admin->id]);
        CashBook::factory()->create(['type' => 'out', 'amount' => 50000, 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->member)
            ->getJson('/api/cash-books')
            ->assertOk();

        $response->assertJsonPath('summary.total_in', 200000);
        $response->assertJsonPath('summary.total_out', 50000);
        $response->assertJsonPath('summary.balance', 150000);
    }

    // --- Store ---

    public function test_admin_can_create_cash_book(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/cash-books', [
                'type' => 'in',
                'amount' => 100000,
                'category' => 'Iuran',
                'description' => 'Iuran bulanan',
                'transaction_date' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Iuran');

        $this->assertDatabaseHas('cash_books', ['title' => 'Iuran', 'type' => 'in']);
    }

    public function test_treasurer_can_create_cash_book(): void
    {
        $this->actingAs($this->treasurer)
            ->postJson('/api/cash-books', [
                'type' => 'out',
                'amount' => 50000,
                'category' => 'Snack',
                'transaction_date' => now()->toDateString(),
            ])
            ->assertCreated();
    }

    public function test_member_cannot_create_cash_book(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/cash-books', [
                'type' => 'in',
                'amount' => 100000,
                'category' => 'Iuran',
                'transaction_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/cash-books', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'amount', 'category', 'transaction_date']);
    }

    public function test_store_rejects_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/cash-books', [
                'type' => 'invalid',
                'amount' => 100000,
                'category' => 'Test',
                'transaction_date' => now()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    // --- Show ---

    public function test_user_can_view_single_cash_book(): void
    {
        $cashBook = CashBook::factory()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson("/api/cash-books/{$cashBook->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $cashBook->id);
    }

    // --- History ---

    public function test_user_can_view_cash_book_history(): void
    {
        $cashBook = CashBook::factory()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson("/api/cash-books/{$cashBook->id}/history")
            ->assertOk();
    }
}
