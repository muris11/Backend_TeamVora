<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->superAdmin->syncRoles('super_admin');

        $this->member = User::factory()->create(['role' => 'member']);
        $this->member->syncRoles('member');
    }

    public function test_super_admin_can_get_settings(): void
    {
        Setting::create(['key' => 'site_name', 'value' => 'TeamVora', 'group' => 'general']);

        $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/platform-settings')
            ->assertOk()
            ->assertJsonPath('data.general.site_name', 'TeamVora');
    }

    public function test_super_admin_can_update_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/platform-settings', [
                'settings' => [
                    'site_name' => 'TeamVora Updated',
                    'contact_email' => 'admin@teamvora.com',
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'TeamVora Updated',
        ]);
    }

    public function test_non_admin_cannot_get_settings(): void
    {
        $this->actingAs($this->member)
            ->getJson('/api/admin/platform-settings')
            ->assertForbidden();
    }

    public function test_system_status_returns_real_data(): void
    {
        $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/system-status')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'php_version',
                    'laravel_version',
                    'db_status',
                    'storage_status',
                    'cache_status',
                    'environment',
                    'debug_mode',
                    'app_name',
                    'app_url',
                    'disk_usage',
                ],
            ]);
    }

    public function test_email_config_returns_mail_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->getJson('/api/email-config')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
