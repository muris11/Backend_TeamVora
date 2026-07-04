<?php

namespace Tests\Feature\Api;

use App\Models\CashBook;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_user_can_get_dashboard_stats(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Member');

        $this->actingAs($user)
            ->getJson('/api/dashboard/stats')
            ->assertOk()
            ->assertJsonStructure([
                'finance' => ['balance', 'monthly_expense', 'total_in', 'total_out'],
                'unpaid_bills',
                'active_tasks',
            ]);
    }

    public function test_unauthenticated_cannot_get_dashboard(): void
    {
        $this->getJson('/api/dashboard/stats')->assertUnauthorized();
    }

    public function test_dashboard_reflects_cashbook_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Member');

        CashBook::factory()->create(['type' => 'in', 'amount' => 100000, 'created_by' => $user->id]);
        CashBook::factory()->create(['type' => 'out', 'amount' => 30000, 'created_by' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/dashboard/stats')
            ->assertOk();

        $response->assertJsonPath('finance.total_in', 100000);
        $response->assertJsonPath('finance.total_out', 30000);
        $response->assertJsonPath('finance.balance', 70000);
    }
}
