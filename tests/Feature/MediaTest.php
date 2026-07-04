<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamMedia;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->team = Team::create([
            'name' => 'Tim Media',
            'slug' => 'tim-media-' . Str::random(5),
        ]);

        $this->user = User::factory()->create([
            'role' => 'member',
            'team_id' => $this->team->id,
        ]);
        $this->user->syncRoles('member');
    }

    public function test_user_can_upload_media(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->user)
            ->postJson('/api/media', [
                'name' => 'Dokumen Penting',
                'type' => 'document',
                'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Dokumen Penting');

        $this->assertDatabaseHas('team_media', [
            'name' => 'Dokumen Penting',
            'type' => 'document',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_list_media(): void
    {
        TeamMedia::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
            'type' => 'document',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/media/documents')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_delete_own_media(): void
    {
        Storage::fake('r2');

        $media = TeamMedia::factory()->create([
            'user_id' => $this->user->id,
            'team_id' => $this->team->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/media/{$media->id}")
            ->assertOk();

        $this->assertDatabaseMissing('team_media', ['id' => $media->id]);
    }

    public function test_unauthenticated_cannot_upload(): void
    {
        $this->postJson('/api/media', [
            'name' => 'Test',
            'type' => 'document',
            'file' => UploadedFile::fake()->create('test.pdf'),
        ])->assertUnauthorized();
    }

    public function test_rejects_oversized_file(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/media', [
                'name' => 'File Besar',
                'type' => 'document',
                'file' => UploadedFile::fake()->create('besar.pdf', 11000),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }
}
