<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lead;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create()->assignRole('Admin');
        $this->lead = User::factory()->create()->assignRole('Lead');
        $this->member = User::factory()->create()->assignRole('Member');
    }

    // --- Index ---

    public function test_admin_can_list_members(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/members')
            ->assertOk()
            ->assertJsonStructure(['users', 'roles', 'permissions']);
    }

    public function test_lead_can_list_members(): void
    {
        $this->actingAs($this->lead)
            ->getJson('/api/members')
            ->assertOk();
    }

    public function test_member_cannot_list_members(): void
    {
        $this->actingAs($this->member)
            ->getJson('/api/members')
            ->assertForbidden();
    }

    // --- Update Role ---

    public function test_admin_can_change_role(): void
    {
        $target = User::factory()->create()->assignRole('Member');

        $this->actingAs($this->admin)
            ->putJson("/api/members/{$target->id}/role", ['role' => 'Treasurer'])
            ->assertOk();

        $this->assertTrue($target->fresh()->hasRole('Treasurer'));
    }

    public function test_cannot_change_own_role(): void
    {
        $this->actingAs($this->admin)
            ->putJson("/api/members/{$this->admin->id}/role", ['role' => 'Member'])
            ->assertUnprocessable();
    }

    public function test_member_cannot_change_role(): void
    {
        $target = User::factory()->create()->assignRole('Member');

        $this->actingAs($this->member)
            ->putJson("/api/members/{$target->id}/role", ['role' => 'Admin'])
            ->assertForbidden();
    }

    // --- Update Permissions ---

    public function test_admin_can_update_user_permissions(): void
    {
        $target = User::factory()->create()->assignRole('Member');

        $this->actingAs($this->admin)
            ->putJson("/api/members/{$target->id}/permissions", [
                'permissions' => ['view_cash_book', 'write_cash_book'],
            ])
            ->assertOk();

        $this->assertTrue($target->fresh()->hasPermissionTo('write_cash_book'));
    }

    // --- Update Role Permissions ---

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::findByName('Member');

        $this->actingAs($this->admin)
            ->putJson("/api/roles/{$role->id}/permissions", [
                'permissions' => ['view_dashboard', 'view_tasks'],
            ])
            ->assertOk();

        $this->assertTrue($role->fresh()->hasPermissionTo('view_tasks'));
    }

    public function test_member_cannot_update_role_permissions(): void
    {
        $role = Role::findByName('Member');

        $this->actingAs($this->member)
            ->putJson("/api/roles/{$role->id}/permissions", [
                'permissions' => ['view_dashboard'],
            ])
            ->assertForbidden();
    }
}
