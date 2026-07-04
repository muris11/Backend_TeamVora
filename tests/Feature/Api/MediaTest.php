<?php

namespace Tests\Feature\Api;

use App\Models\TeamMedia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create()->assignRole('Admin');
        $this->member = User::factory()->create()->assignRole('Member');
    }

    // --- Documents ---

    public function test_user_can_list_documents(): void
    {
        TeamMedia::factory()->count(3)->create(['type' => 'document']);

        $this->actingAs($this->member)
            ->getJson('/api/media/documents')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_upload_document(): void
    {
        Storage::fake('s3');

        $this->actingAs($this->member)
            ->postJson('/api/media', [
                'name' => 'Laporan',
                'type' => 'document',
                'file' => UploadedFile::fake()->create('laporan.pdf', 100),
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Laporan');

        $this->assertDatabaseHas('team_media', ['name' => 'Laporan', 'type' => 'document']);
    }

    // --- Gallery ---

    public function test_user_can_list_gallery(): void
    {
        TeamMedia::factory()->count(4)->create(['type' => 'gallery']);

        $this->actingAs($this->member)
            ->getJson('/api/media/gallery')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_user_can_upload_gallery_image(): void
    {
        Storage::fake('s3');

        $this->actingAs($this->member)
            ->postJson('/api/media', [
                'name' => 'Foto Tim',
                'type' => 'gallery',
                'file' => UploadedFile::fake()->image('foto.jpg'),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('team_media', ['type' => 'gallery']);
    }

    public function test_upload_validates_type(): void
    {
        $this->actingAs($this->member)
            ->postJson('/api/media', [
                'name' => 'Test',
                'type' => 'invalid',
                'file' => UploadedFile::fake()->create('test.pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    // --- Delete ---

    public function test_owner_can_delete(): void
    {
        Storage::fake('s3');
        $media = TeamMedia::factory()->create(['user_id' => $this->member->id]);

        $this->actingAs($this->member)
            ->deleteJson("/api/media/{$media->id}")
            ->assertOk();

        $this->assertDatabaseMissing('team_media', ['id' => $media->id]);
    }

    public function test_admin_can_delete_others_media(): void
    {
        Storage::fake('s3');
        $media = TeamMedia::factory()->create(['user_id' => $this->member->id]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/media/{$media->id}")
            ->assertOk();
    }

    public function test_non_owner_non_admin_cannot_delete(): void
    {
        $other = User::factory()->create()->assignRole('Member');
        $media = TeamMedia::factory()->create(['user_id' => $other->id]);

        $this->actingAs($this->member)
            ->deleteJson("/api/media/{$media->id}")
            ->assertForbidden();
    }
}
