<?php

namespace Tests\Feature\Api;

use App\Models\DailyLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->member = User::factory()->create()->assignRole('member');
    }

    public function test_user_can_list_logs(): void
    {
        DailyLog::factory()->count(3)->create(['user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->getJson('/api/daily-logs')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_log(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/daily-logs', [
                'title' => 'Hari pertama',
                'content' => 'Hari ini saya belajar Laravel API.',
                'log_date' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Hari pertama');

        $this->assertDatabaseHas('daily_logs', ['title' => 'Hari pertama']);
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/daily-logs', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'log_date']);
    }

    public function test_user_can_update_own_log(): void
    {
        $log = DailyLog::factory()->create(['user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->putJson("/api/daily-logs/{$log->id}", [
                'title' => 'Updated',
                'content' => 'Updated content',
            ])
            ->assertOk();

        $this->assertEquals('Updated', $log->fresh()->title);
    }

    public function test_user_cannot_update_others_log(): void
    {
        $other = User::factory()->create()->assignRole('member');
        $log = DailyLog::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->member)
            ->putJson("/api/daily-logs/{$log->id}", [
                'title' => 'Hack',
                'content' => 'Hack',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_log(): void
    {
        $log = DailyLog::factory()->create([
            'user_id' => $this->member->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->member)
            ->deleteJson("/api/daily-logs/{$log->id}")
            ->assertOk();

        $this->assertDatabaseMissing('daily_logs', ['id' => $log->id]);
    }

    public function test_cannot_delete_log_older_than_3_days(): void
    {
        $log = DailyLog::factory()->create([
            'user_id' => $this->member->id,
            'created_at' => now()->subDays(4),
        ]);

        $this->actingAs($this->member)
            ->deleteJson("/api/daily-logs/{$log->id}")
            ->assertUnprocessable();
    }

    public function test_user_can_export_data(): void
    {
        DailyLog::factory()->count(2)->create(['user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->getJson('/api/daily-logs/export')
            ->assertOk()
            ->assertJsonStructure(['user', 'logs']);
    }
}
