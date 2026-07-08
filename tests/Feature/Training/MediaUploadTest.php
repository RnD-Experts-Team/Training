<?php

namespace Tests\Feature\Training;

use App\Enums\MediaType;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_upload_an_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($admin)->post(route('training.media.store', $item), [
            'type' => MediaType::Image->value,
            'file' => UploadedFile::fake()->image('day-dot.jpg'),
            'label' => 'Day Dot placement',
        ])->assertSessionHasNoErrors();

        $media = $item->media()->sole();
        $this->assertSame(MediaType::Image, $media->type);
        $this->assertNotNull($media->path);
        $this->assertNull($media->url);
        Storage::disk('public')->assertExists($media->path);

        // Managers/admins open the file via an origin-relative public URL.
        $this->assertStringStartsWith('/storage/', (string) $media->display_url);
        $this->assertStringContainsString($media->path, (string) $media->display_url);
    }

    public function test_super_admin_can_add_a_link(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($admin)->post(route('training.media.store', $item), [
            'type' => MediaType::Link->value,
            'url' => 'https://littlecaesars.read.inkling.com/example',
            'label' => 'Reference',
        ])->assertSessionHasNoErrors();

        $media = $item->media()->sole();
        $this->assertSame(MediaType::Link, $media->type);
        $this->assertSame('https://littlecaesars.read.inkling.com/example', $media->url);
        $this->assertNull($media->path);
        // A link opens at its external URL unchanged.
        $this->assertSame($media->url, $media->display_url);
    }

    public function test_oversized_image_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($admin)->post(route('training.media.store', $item), [
            'type' => MediaType::Image->value,
            'file' => UploadedFile::fake()->image('huge.jpg')->size(6000),
        ])->assertSessionHasErrors('file');
    }

    public function test_wrong_mime_for_image_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($admin)->post(route('training.media.store', $item), [
            'type' => MediaType::Image->value,
            'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    public function test_link_without_url_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($admin)->post(route('training.media.store', $item), [
            'type' => MediaType::Link->value,
        ])->assertSessionHasErrors('url');
    }

    public function test_managers_cannot_add_media(): void
    {
        $manager = User::factory()->manager()->create();
        $item = ChecklistItem::factory()->create();

        $this->actingAs($manager)->post(route('training.media.store', $item), [
            'type' => MediaType::Link->value,
            'url' => 'https://example.com',
        ])->assertForbidden();
    }
}
