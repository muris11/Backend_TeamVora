<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    // --- Login ---

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@teamvora.local',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('Member');

        $response = $this->postJson('/api/login', [
            'email' => 'test@teamvora.local',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'roles'],
                'token',
            ])
            ->assertJsonFragment(['email' => 'test@teamvora.local']);
    }

    public function test_login_rejects_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@teamvora.local',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'test@teamvora.local',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_login_rejects_nonexistent_email(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@teamvora.local',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_login_validates_required_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // --- Register ---

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new@teamvora.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'new@teamvora.local']);
    }

    public function test_register_validates_required_fields(): void
    {
        $this->postJson('/api/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@teamvora.local']);

        $this->postJson('/api/register', [
            'name' => 'Duplicate',
            'email' => 'existing@teamvora.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // --- Me ---

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Member');

        $this->actingAs($user)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_unauthenticated_cannot_get_profile(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    // --- Logout ---

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // --- Update Profile ---

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->putJson('/api/profile', [
                'name' => 'New Name',
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertEquals('New Name', $user->fresh()->name);
    }

    // --- Update Password ---

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-pass')]);

        $this->actingAs($user)
            ->putJson('/api/password', [
                'current_password' => 'old-pass',
                'password' => 'new-pass-123',
                'password_confirmation' => 'new-pass-123',
            ])
            ->assertOk();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-pass-123', $user->fresh()->password));
    }

    public function test_update_password_rejects_wrong_current(): void
    {
        $user = User::factory()->create(['password' => bcrypt('old-pass')]);

        $this->actingAs($user)
            ->putJson('/api/password', [
                'current_password' => 'wrong-pass',
                'password' => 'new-pass-123',
                'password_confirmation' => 'new-pass-123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}
