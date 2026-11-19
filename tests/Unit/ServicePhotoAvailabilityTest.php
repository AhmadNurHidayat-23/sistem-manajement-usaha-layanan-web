<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicePhotoAvailabilityTest extends TestCase
{
    public function test_landing_renders_only_available_photos_in_gallery_order(): void
    {
        $disk = Storage::fake('public');
        $disk->put('services/damaged.jpg', 'invalid jpeg');
        $disk->putFileAs('services', UploadedFile::fake()->image('second.jpg'), 'second.jpg');
        $disk->putFileAs('services', UploadedFile::fake()->image('third.jpg'), 'third.jpg');

        $service = new Service(['name' => 'Layanan dengan galeri', 'price' => 10000]);
        $service->id = 91;
        $service->setRelation('category', null);
        $service->setRelation('branch', null);
        $service->setRelation('images', collect([
            new ServiceImage(['image_path' => 'services/missing.jpg']),
            new ServiceImage(['image_path' => 'services/second.jpg']),
            new ServiceImage(['image_path' => 'services/damaged.jpg']),
            new ServiceImage(['image_path' => 'services/third.jpg']),
        ]));

        $html = view('landing.partials.layanan', ['services' => collect([$service])])->render();

        $this->assertSame(1, substr_count($html, '<img '));
        $this->assertStringNotContainsString('missing.jpg', $html);
        $this->assertStringNotContainsString('damaged.jpg', $html);
        $this->assertStringContainsString('second.jpg', $html);
        $this->assertStringNotContainsString('third.jpg', $html);
    }

    public function test_missing_cover_does_not_emit_a_placeholder_or_broken_url(): void
    {
        Storage::fake('public');

        $service = new Service(['name' => 'Layanan tanpa foto', 'price' => 10000, 'image_path' => 'services/cover.jpg']);
        $service->id = 92;
        $service->setRelation('category', null);
        $service->setRelation('branch', null);
        $service->setRelation('images', collect());

        $html = view('landing.partials.layanan', ['services' => collect([$service])])->render();

        $this->assertNull($service->image_url);
        $this->assertStringContainsString('Layanan tanpa foto', $html);
        $this->assertStringNotContainsString('<img ', $html);
        $this->assertStringNotContainsString('service-carousel', $html);
    }
}
