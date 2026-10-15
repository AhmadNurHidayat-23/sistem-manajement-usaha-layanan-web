<?php

namespace Tests\Feature\Katalog;

use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use App\Services\Katalog\LayananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedServiceUniversalTest extends TestCase
{
    use RefreshDatabase;

    protected LayananService $layananService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->layananService = app(LayananService::class);
    }

    public function test_featured_service_tidak_mengecualikan_layanan_salon_atau_kendaraan(): void
    {
        $category = ServiceCategory::create(['name' => 'Auto Salon & Kendaraan']);
        $branch = Branch::create([
            'name' => 'Auto Detailing & Salon Mobil',
            'address' => 'Jl. Otomotif No. 12',
            'phone' => '081234567890',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Full Detailing Salon Kendaraan',
            'description' => 'Perawatan kendaraan komprehensif',
            'price' => 500000,
            'unit' => 'unit',
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $featured = $this->layananService->getFeaturedService($branch->id);

        $this->assertNotNull($featured);
        $this->assertEquals($service->id, $featured->id);
        $this->assertStringContainsString('Salon Kendaraan', $featured->name);
    }

    public function test_featured_service_memprioritaskan_layanan_dengan_rating_ulasan_tertinggi(): void
    {
        $category = ServiceCategory::create(['name' => 'Layanan Umum']);
        $branch = Branch::create([
            'name' => 'Cabang Universal',
            'address' => 'Jl. Layanan No. 5',
            'phone' => '081298765432',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $serviceA = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Layanan Biasa Harga Mahal',
            'price' => 700000,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $serviceB = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Layanan Favorit Rating Tinggi',
            'price' => 300000,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        $transaction = Transaction::create([
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'transaction_code' => 'TRX-TEST-002',
            'subtotal' => 300000,
            'discount' => 0,
            'total' => 300000,
            'payment_method' => 'cash',
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        Ulasan::create([
            'transaction_id' => $transaction->id,
            'service_id' => $serviceB->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Sangat memuaskan dan profesional!',
            'is_visible' => true,
        ]);

        $featured = $this->layananService->getFeaturedService($branch->id);

        $this->assertNotNull($featured);
        $this->assertEquals($serviceB->id, $featured->id);
        $this->assertEquals('Layanan Favorit Rating Tinggi', $featured->name);
    }
}
