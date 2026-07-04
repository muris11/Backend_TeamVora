<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->superAdmin->syncRoles('super_admin');
    }

    public function test_anyone_can_submit_contact(): void
    {
        $this->postJson('/api/contact', [
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi@example.com',
            'company' => 'PT Maju',
            'message' => 'Halo, saya ingin bertanya tentang layanan Anda.',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Pesan berhasil dikirim.');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'budi@example.com',
            'first_name' => 'Budi',
        ]);
    }

    public function test_contact_validates_required_fields(): void
    {
        $this->postJson('/api/contact', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'message']);
    }

    public function test_super_admin_can_list_contacts(): void
    {
        ContactMessage::create([
            'first_name' => 'Budi', 'last_name' => 'Santoso',
            'email' => 'budi-list@example.com', 'message' => 'Pesan pertama untuk testing list.',
        ]);
        ContactMessage::create([
            'first_name' => 'Andi', 'last_name' => 'Wijaya',
            'email' => 'andi-list@example.com', 'message' => 'Pesan kedua untuk testing list.',
        ]);
        ContactMessage::create([
            'first_name' => 'Sari', 'last_name' => 'Devi',
            'email' => 'sari-list@example.com', 'message' => 'Pesan ketiga untuk testing list.',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/contact')
            ->assertOk();

        $body = $response->json();
        $this->assertArrayHasKey('data', $body);
        $items = is_array($body['data']) && isset($body['data']['data'])
            ? $body['data']['data']
            : $body['data'];
        $emails = collect($items)->pluck('email')->toArray();
        $this->assertContains('budi-list@example.com', $emails);
        $this->assertContains('andi-list@example.com', $emails);
        $this->assertContains('sari-list@example.com', $emails);
    }

    public function test_super_admin_can_mark_read(): void
    {
        $message = ContactMessage::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'message' => 'Test message content here.',
            'is_read' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson("/api/contact/{$message->id}/read")
            ->assertOk();

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }
}
