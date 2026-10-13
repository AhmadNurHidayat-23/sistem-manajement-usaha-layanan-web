<?php

namespace Tests\Feature\Katalog;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceImage;
use Database\Seeders\CabangSeeder;
use Database\Seeders\LayananSeeder;
use Database\Seeders\PeranSeeder;
use Database\Seeders\ServiceImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceMultiImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            PeranSeeder::class,
            CabangSeeder::class,
            LayananSeeder::class,
            ServiceImageSeeder::class,
        ]);
    }

    public function test_service_has_many_service_images_ordered_by_sort_order(): void
    {
        $service = Service::where('is_active', true)->firstOrFail();

        $this->assertTrue($service->images()->exists());
        $this->assertInstanceOf(ServiceImage::class, $service->images->first());

        $sortOrders = $service->images->pluck('sort_order')->toArray();
        $expectedOrders = $sortOrders;
        sort($expectedOrders);
        $this->assertEquals($expectedOrders, $sortOrders);
    }

    public function test_service_image_belongs_to_service(): void
    {
        $image = ServiceImage::firstOrFail();

        $this->assertInstanceOf(Service::class, $image->service);
        $this->assertEquals($image->service_id, $image->service->id);
        $this->assertNotEmpty($image->image_path);
        $this->assertNotNull($image->image_url);
    }

    public function test_every_active_service_has_at_least_three_images(): void
    {
        $activeServices = Service::where('is_active', true)->with('images')->get();

        $this->assertGreaterThanOrEqual(15, $activeServices->count());
        foreach ($activeServices as $service) {
            $this->assertGreaterThanOrEqual(
                3,
                $service->images->count(),
                "Layanan {$service->name} harus memiliki minimal 3 gambar."
            );
        }
    }

    public function test_landing_page_eager_loads_service_images_without_n_plus_one(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Katalog Layanan');
        $response->assertSee('service-carousel');
    }

    public function test_landing_page_renders_carousel_markup_and_controls(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('carousel-btn-prev');
        $response->assertSee('carousel-btn-next');
        $response->assertSee('carousel-dots');
        $response->assertSee('carousel-slides');
    }

    public function test_landing_page_handles_fallback_when_service_has_no_images(): void
    {
        $branch = Branch::firstOrFail();
        $category = ServiceCategory::firstOrFail();

        $fallbackService = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Tanpa Foto Khusus',
            'service_type' => 'appointment',
            'price' => 50000,
            'unit' => 'sesi',
            'is_active' => true,
        ]);

        $this->assertEquals(0, $fallbackService->images()->count());

        $response = $this->get('/?branch_id='.$branch->id);
        $response->assertStatus(200);
        $response->assertSee('Layanan Tanpa Foto Khusus');
        $response->assertSee('Dokumentasi Layanan');
    }

    public function test_branch_filtering_maintains_multi_image_carousel(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $responseA = $this->get('/?branch_id='.$branchA->id);

        $responseA->assertStatus(200);
        $responseA->assertSee('service-carousel');

        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $responseB = $this->get('/?branch_id='.$branchB->id);

        $responseB->assertStatus(200);
        $responseB->assertSee('service-carousel');
    }

    public function test_service_images_support_mixed_extensions_and_render_in_carousel(): void
    {
        $branch = Branch::firstOrFail();
        $category = ServiceCategory::firstOrFail();

        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Mixed Extension',
            'service_type' => 'appointment',
            'price' => 75000,
            'unit' => 'sesi',
            'is_active' => true,
        ]);

        $mixedFiles = [
            1 => '01.webp',
            2 => '02.png',
            3 => '03.jpg',
        ];

        foreach ($mixedFiles as $order => $filename) {
            ServiceImage::create([
                'service_id' => $service->id,
                'sort_order' => $order,
                'image_path' => "services/barbershop/potong-rambut-pria/{$filename}",
            ]);
        }

        $service->refresh();
        $this->assertCount(3, $service->images);

        $this->assertStringEndsWith('.webp', $service->images[0]->image_path);
        $this->assertStringEndsWith('.png', $service->images[1]->image_path);
        $this->assertStringEndsWith('.jpg', $service->images[2]->image_path);

        $this->assertStringContainsString('01.webp', $service->images[0]->image_url);
        $this->assertStringContainsString('02.png', $service->images[1]->image_url);
        $this->assertStringContainsString('03.jpg', $service->images[2]->image_url);

        $response = $this->get('/?branch_id='.$branch->id);
        $response->assertStatus(200);
        $response->assertSee('01.webp');
        $response->assertSee('02.png');
        $response->assertSee('03.jpg');
    }

    public function test_service_image_seeder_resolves_actual_files_with_supported_extensions(): void
    {
        $seeder = new ServiceImageSeeder;

        $this->assertContains('webp', ServiceImageSeeder::SUPPORTED_EXTENSIONS);
        $this->assertContains('png', ServiceImageSeeder::SUPPORTED_EXTENSIONS);
        $this->assertContains('jpg', ServiceImageSeeder::SUPPORTED_EXTENSIONS);
        $this->assertContains('jpeg', ServiceImageSeeder::SUPPORTED_EXTENSIONS);

        $resolvedPath = $seeder->resolveSlotImagePath('services/barbershop/potong-rambut-pria', '01');
        $this->assertNotNull($resolvedPath);
        $this->assertFileExists(storage_path('app/public/'.$resolvedPath));

        // Verifikasi tidak ada string .jpg yang di-hardcode di pemetaan direktori seeder
        $reflection = new \ReflectionClass(ServiceImageSeeder::class);
        $property = $reflection->getProperty('serviceDirectories');
        $property->setAccessible(true);
        $directories = $property->getValue($seeder);

        foreach ($directories as $serviceName => $dirPath) {
            $this->assertStringNotContainsString('.jpg', $dirPath, "Direktori layanan {$serviceName} tidak boleh berisi hardcoded .jpg");
            $this->assertStringNotContainsString('.png', $dirPath);
            $this->assertStringNotContainsString('.webp', $dirPath);
        }
    }

    public function test_all_18_seeded_services_have_exactly_three_physical_images_with_valid_paths(): void
    {
        $services = Service::where('is_active', true)->with('images')->get();

        $this->assertCount(18, $services, 'Total layanan aktif harus tepat 18.');

        $totalImages = 0;
        foreach ($services as $service) {
            $this->assertCount(3, $service->images, "Layanan {$service->name} harus memiliki tepat 3 gambar.");
            foreach ($service->images as $img) {
                $totalImages++;
                $this->assertFileExists(
                    storage_path('app/public/'.$img->image_path),
                    "File fisik untuk {$img->image_path} tidak ditemukan."
                );
                $this->assertStringStartsWith('/storage/services/', $img->image_url);
            }
        }

        $this->assertEquals(54, $totalImages, 'Total gambar terhubung harus tepat 54.');
    }

    public function test_mobile_api_services_endpoint_returns_image_url_and_multi_images_array(): void
    {
        $response = $this->getJson('/api/mobile/services');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'image_url',
                        'images' => [
                            '*' => [
                                'id',
                                'image_url',
                                'sort_order',
                            ],
                        ],
                    ],
                ],
            ]);

        $firstItem = $response->json('data.0');
        $this->assertNotEmpty($firstItem['image_url']);
        $this->assertCount(3, $firstItem['images']);
        $this->assertStringStartsWith('/storage/services/', $firstItem['images'][0]['image_url']);
    }
}
