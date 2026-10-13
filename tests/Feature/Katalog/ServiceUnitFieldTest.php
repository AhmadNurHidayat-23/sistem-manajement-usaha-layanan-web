<?php

namespace Tests\Feature\Katalog;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceUnitFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_menyimpan_dan_mengambil_field_unit(): void
    {
        $category = ServiceCategory::create(['name' => 'Barbershop']);
        $branch = Branch::create([
            'name' => 'Cabang Barbershop',
            'address' => 'Jl. Pangkas No. 10',
            'phone' => '081234567890',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Haircut Modern',
            'price' => 75000,
            'unit' => 'sesi',
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'unit' => 'sesi',
        ]);
        $this->assertEquals('sesi', $service->fresh()->unit);
    }

    public function test_owner_dapat_membuat_layanan_dengan_unit(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $category = ServiceCategory::create(['name' => 'Otomotif']);
        $branch = Branch::create([
            'name' => 'Cabang Auto Care',
            'address' => 'Jl. Otomotif No. 99',
            'phone' => '081234567891',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post(route('catalog.services.store'), [
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Coating Mobil 9H',
            'price' => 1500000,
            'unit' => 'kendaraan',
            'estimated_duration' => 180,
            'service_type' => 'single',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Coating Mobil 9H',
            'unit' => 'kendaraan',
        ]);
    }

    public function test_landing_page_merender_unit_dari_data_service(): void
    {
        $category = ServiceCategory::create(['name' => 'Sepatu Care']);
        $branch = Branch::create([
            'name' => 'Cabang Custom',
            'address' => 'Jl. Sepatu No. 1',
            'phone' => '081234567892',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Cuci Sepatu Premium',
            'price' => 85000,
            'unit' => 'pasang',
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/ pasang');
    }
}
