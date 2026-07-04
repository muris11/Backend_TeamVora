<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $teamLeader;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->superAdmin->syncRoles('super_admin');

        $this->teamLeader = User::factory()->create(['role' => 'team_leader']);
        $this->teamLeader->syncRoles('team_leader');

        $this->member = User::factory()->create(['role' => 'member']);
        $this->member->syncRoles('member');
    }

    protected function createTeam(array $overrides = []): Team
    {
        $name = $overrides['name'] ?? fake()->unique()->word();
        return Team::create(array_merge([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'leader_id' => $this->teamLeader->id,
        ], $overrides));
    }

    public function test_super_admin_can_create_team(): void
    {
        $leader = User::factory()->create(['role' => 'member']);

        $this->actingAs($this->superAdmin)
            ->postJson('/api/teams', [
                'name' => 'Tim Baru',
                'description' => 'Deskripsi tim',
                'leader_id' => $leader->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Tim Baru');

        $this->assertDatabaseHas('teams', ['name' => 'Tim Baru']);
    }

    public function test_team_leader_can_view_members(): void
    {
        $team = $this->createTeam(['name' => 'Tim View', 'slug' => 'tim-view']);
        $this->teamLeader->update(['team_id' => $team->id]);

        $memberInTeam = User::factory()->create(['team_id' => $team->id, 'role' => 'member']);
        $memberInTeam->syncRoles('member');

        $this->actingAs($this->teamLeader)
            ->getJson("/api/teams/{$team->id}/members")
            ->assertOk();
    }

    public function test_team_leader_can_invite_member(): void
    {
        $team = $this->createTeam(['name' => 'Tim Invite', 'slug' => 'tim-invite']);
        $this->teamLeader->update(['team_id' => $team->id]);

        $userToInvite = User::factory()->create(['role' => 'member']);
        $userToInvite->syncRoles('member');

        $this->actingAs($this->teamLeader)
            ->postJson("/api/teams/{$team->id}/invite", [
                'user_id' => $userToInvite->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $userToInvite->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_team_leader_can_remove_member(): void
    {
        $team = $this->createTeam(['name' => 'Tim Remove', 'slug' => 'tim-remove']);
        $this->teamLeader->update(['team_id' => $team->id]);

        $memberToRemove = User::factory()->create(['team_id' => $team->id, 'role' => 'member']);
        $memberToRemove->syncRoles('member');

        $this->actingAs($this->teamLeader)
            ->deleteJson("/api/teams/{$team->id}/members/{$memberToRemove->id}")
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $memberToRemove->id,
            'team_id' => null,
        ]);
    }

    public function test_member_cannot_create_team(): void
    {
        $leader = User::factory()->create(['role' => 'member']);

        $this->actingAs($this->member)
            ->postJson('/api/teams', [
                'name' => 'Tim Gagal',
                'leader_id' => $leader->id,
            ])
            ->assertForbidden();
    }

    public function test_team_leader_can_update_member(): void
    {
        $team = $this->createTeam(['name' => 'Tim Update', 'slug' => 'tim-update']);
        $this->teamLeader->update(['team_id' => $team->id]);

        $memberToUpdate = User::factory()->create([
            'team_id' => $team->id,
            'role' => 'member',
            'name' => 'Old Name',
        ]);
        $memberToUpdate->syncRoles('member');

        $this->actingAs($this->teamLeader)
            ->putJson("/api/teams/{$team->id}/members/{$memberToUpdate->id}/update", [
                'name' => 'New Name',
            ])
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $memberToUpdate->id,
            'name' => 'New Name',
        ]);
    }
}
