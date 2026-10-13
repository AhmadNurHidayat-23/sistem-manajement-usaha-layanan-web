<?php

namespace Tests\Feature\Customer;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Modules\BookingAntrian\Services\BookingService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderBranchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branchPusat;

    protected Branch $branchSelatan;

    protected Branch $branchBarat;

    protected Service $laundryService;

    protected Service $laundryBedCoverService;

    protected Service $barbershopService;

    protected Service $autoCareService;

    protected Service $nullBranchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $this->branchPusat = Branch::create([
            'name' => 'SIMAUL Barbershop (Cabang Pusat)',
            'address' => 'Jl. Sudirman No. 10',
            'is_active' => true,
        ]);

        $this->branchSelatan = Branch::create([
            'name' => 'SIMAUL Laundry (Cabang Selatan)',
            'address' => 'Jl. Fatmawati No. 25',
            'is_active' => true,
        ]);

        $this->branchBarat = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Panjang No. 88',
            'is_active' => true,
        ]);

        $barberCategory = ServiceCategory::create(['name' => 'Potong Rambut & Styling']);
        $this->barbershopService = Service::create([
            'service_category_id' => $barberCategory->id,
            'branch_id' => $this->branchPusat->id,
            'name' => 'Potong Rambut Pria',
            'description' => 'Potong rambut rapi berkelas.',
            'price' => 95000,
            'unit' => 'sesi',
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $laundryCategory = ServiceCategory::create(['name' => 'Laundry Kiloan & Reguler']);
        $this->laundryService = Service::create([
            'service_category_id' => $laundryCategory->id,
            'branch_id' => $this->branchSelatan->id,
            'name' => 'Cuci Kering Setrika',
            'description' => 'Cuci, kering, dan setrika rapi.',
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 1440,
            'is_active' => true,
        ]);

        $this->laundryBedCoverService = Service::create([
            'service_category_id' => $laundryCategory->id,
            'branch_id' => $this->branchSelatan->id,
            'name' => 'Cuci Bed Cover',
            'description' => 'Pencucian tebal bed cover premium.',
            'price' => 50000,
            'unit' => 'item',
            'service_type' => 'drop_off',
            'estimated_duration' => 2880,
            'is_active' => true,
        ]);

        $autoCategory = ServiceCategory::create(['name' => 'Cuci Mobil & Interior']);
        $this->autoCareService = Service::create([
            'service_category_id' => $autoCategory->id,
            'branch_id' => $this->branchBarat->id,
            'name' => 'Cuci Mobil Premium',
            'description' => 'Cuci mobil menyeluruh.',
            'price' => 85000,
            'unit' => 'unit',
            'service_type' => 'drop_off',
            'estimated_duration' => 90,
            'is_active' => true,
        ]);

        $this->nullBranchService = Service::create([
            'service_category_id' => $autoCategory->id,
            'branch_id' => null,
            'name' => 'Layanan Global Tanpa Cabang',
            'description' => 'Layanan dummy tidak boleh muncul di cabang manapun.',
            'price' => 20000,
            'unit' => 'item',
            'service_type' => 'drop_off',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_access_home_catalog(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.home'));

        $response->assertStatus(200);
        $response->assertSee('SIMAUL');
        $response->assertSee('Cuci Kering Setrika');
        $response->assertSee('Potong Rambut Pria');
    }

    public function test_customer_can_view_service_detail(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.layanan.show', $this->laundryService));

        $response->assertStatus(200);
        $response->assertSee($this->laundryService->name);
        $response->assertSee('SIMAUL Laundry (Cabang Selatan)');
        $response->assertSee('Pesan Sekarang');
    }

    public function test_cabang_barbershop_tidak_memiliki_layanan_laundry_atau_auto_care(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('customer.pesanan.branch-services', [
            'branch_id' => $this->branchPusat->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => (string) $this->barbershopService->id]);
        $response->assertJsonMissing(['id' => (string) $this->laundryService->id]);

        $viewResponse = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchPusat->id,
        ]));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Potong Rambut Pria');
        $viewResponse->assertDontSee('Cuci Kering Setrika');
    }

    public function test_cabang_laundry_tidak_memiliki_layanan_barbershop_atau_auto_care(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('customer.pesanan.branch-services', [
            'branch_id' => $this->branchSelatan->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['id' => (string) $this->laundryService->id]);
        $response->assertJsonMissing(['id' => (string) $this->barbershopService->id]);

        $viewResponse = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchSelatan->id,
        ]));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Cuci Kering Setrika');
        $viewResponse->assertDontSee('Potong Rambut Pria');
    }

    public function test_cabang_auto_care_tidak_memiliki_layanan_barbershop_atau_laundry(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('customer.pesanan.branch-services', [
            'branch_id' => $this->branchBarat->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => (string) $this->autoCareService->id]);
        $response->assertJsonMissing(['id' => (string) $this->barbershopService->id]);
        $response->assertJsonMissing(['id' => (string) $this->laundryService->id]);

        $viewResponse = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchBarat->id,
        ]));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Cuci Mobil Premium');
        $viewResponse->assertDontSee('Cuci Kering Setrika');
    }

    public function test_tidak_ada_service_branch_id_null_yang_ikut_muncul(): void
    {
        $pusatServices = app(BookingService::class)->getActiveServices($this->branchPusat->id);
        $selatanServices = app(BookingService::class)->getActiveServices($this->branchSelatan->id);
        $baratServices = app(BookingService::class)->getActiveServices($this->branchBarat->id);

        $this->assertFalse($pusatServices->contains('id', $this->nullBranchService->id));
        $this->assertFalse($selatanServices->contains('id', $this->nullBranchService->id));
        $this->assertFalse($baratServices->contains('id', $this->nullBranchService->id));
    }
}
