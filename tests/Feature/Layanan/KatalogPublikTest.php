<?php

namespace Tests\Feature\Layanan;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_public_can_view_marketplace_catalog_and_filter(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Cuci & Dry Clean',
            'description' => 'Kategori cuci pakaian',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Utama No 1',
            'is_active' => true,
        ]);

        Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Cuci Reguler 3 Hari',
            'price' => 25000,
            'estimated_duration' => 60,
            'service_type' => 'unit',
            'is_active' => true,
        ]);

        Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Cuci Expres Nonaktif',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'unit',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.catalog'));
        $response->assertStatus(200);
        $response->assertSee('Cuci Reguler 3 Hari');
        $response->assertDontSee('Cuci Expres Nonaktif');

        $responseSearch = $this->get(route('public.catalog', ['q' => 'Reguler']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Cuci Reguler 3 Hari');

        $responseCategory = $this->get(route('public.catalog', ['category_id' => $category->id]));
        $responseCategory->assertStatus(200);
        $responseCategory->assertSee('Cuci Reguler 3 Hari');
    }

    public function test_customer_cannot_access_admin_catalog(): void
    {
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $customerRole->id]);

        $response = $this->actingAs($customer)->get(route('catalog.services.index'));
        $response->assertStatus(403);
    }

    public function test_customer_and_guest_can_view_customer_service_detail(): void
    {
        $category = ServiceCategory::create(['name' => 'Cuci Express', 'is_active' => true]);
        $branch = Branch::create(['name' => 'Cabang Baru', 'address' => 'Jl. Baru', 'is_active' => true]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Cuci Kilat 1 Jam',
            'price' => 35000,
            'estimated_duration' => 60,
            'service_type' => 'unit',
            'is_active' => true,
        ]);

        $responseGuest = $this->get(route('customer.layanan.show', $service->id));
        $responseGuest->assertStatus(200);
        $responseGuest->assertSee('Cuci Kilat 1 Jam');

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $customerRole->id]);
        $responseCustomer = $this->actingAs($customer)->get(route('customer.layanan.show', $service->id));
        $responseCustomer->assertStatus(200);
        $responseCustomer->assertSee('Cuci Kilat 1 Jam');
    }
}
