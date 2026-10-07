<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_uploads_images_in_jodit_format(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('panel.editor.images'), ['images' => [UploadedFile::fake()->image('foto.jpg')]])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.isImages.0', true);

        $url = $response->json('data.files.0');

        $this->assertStringStartsWith('/storage/uploads/editor/', $url);
        Storage::disk('public')->assertExists(substr($url, strlen('/storage/')));
    }

    public function test_rejects_files_that_are_not_images(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('panel.editor.images'), ['images' => [UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf')]])
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.messages.0', 'El archivo debe ser una imagen.');
    }

    public function test_guest_cannot_upload(): void
    {
        $this->post(route('panel.editor.images'), ['images' => [UploadedFile::fake()->image('foto.jpg')]])
            ->assertRedirect(route('login'));
    }
}
