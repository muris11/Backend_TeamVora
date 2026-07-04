<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Notification::fake();
        $this->admin = User::factory()->create()->assignRole('Admin');
        $this->member = User::factory()->create()->assignRole('Member');
    }

    public function test_user_can_list_tasks(): void
    {
        Task::factory()->count(3)->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->member)
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_task(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/tasks', [
                'title' => 'Beli snack',
                'description' => 'Beli snack untuk rapat',
                'assignee_id' => $this->member->id,
                'priority' => 'high',
                'due_date' => now()->addDays(3)->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Beli snack');

        $this->assertDatabaseHas('tasks', ['title' => 'Beli snack', 'status' => 'todo']);
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/tasks', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'priority']);
    }

    public function test_user_can_update_task_status(): void
    {
        $task = Task::factory()->create(['creator_id' => $this->admin->id, 'status' => 'todo']);

        $this->actingAs($this->member)
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'in_progress'])
            ->assertOk();

        $this->assertEquals('in_progress', $task->fresh()->status);
    }

    public function test_update_status_rejects_invalid(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->member)
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'bogus'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->create(['creator_id' => $this->admin->id, 'title' => 'Old']);

        $this->actingAs($this->admin)
            ->putJson("/api/tasks/{$task->id}", ['title' => 'New', 'priority' => 'low'])
            ->assertOk();

        $this->assertEquals('New', $task->fresh()->title);
    }

    public function test_creator_can_delete_task(): void
    {
        $task = Task::factory()->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertOk();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_non_creator_non_admin_cannot_delete(): void
    {
        $task = Task::factory()->create(['creator_id' => $this->admin->id]);

        $this->actingAs($this->member)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertForbidden();
    }
}
