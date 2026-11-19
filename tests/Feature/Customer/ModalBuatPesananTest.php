<?php

namespace Tests\Feature\Customer;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalBuatPesananTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected ServiceCategory $category;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. RS Fatmawati No. 45',
            'service_mode' => 'drop_off',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 8,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_active' => true,
        ]);

        $this->category = ServiceCategory::create([
            'name' => 'Laundry Kiloan',
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $this->category->id,
            'name' => 'Cuci Komplit Reguler',
            'price' => 10000,
            'is_active' => true,
            'service_type' => 'drop_off',
        ]);
    }

    public function test_dashboard_pelanggan_memiliki_modal_buat_pesanan_dan_tombol_trigger(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="modal-buat-pesanan"', false);
        $response->assertSee('data-action="open-order-modal"', false);
        $response->assertSee('data-modal-target="modal-buat-pesanan"', false);
        $response->assertSee('data-branch-id="'.$this->branch->id.'"', false);
        $response->assertSee('data-service-id="'.$this->service->id.'"', false);
        $response->assertSee('Buat Pesanan', false);
        $response->assertSee('id="pickup-map"', false);
    }

    public function test_akses_langsung_halaman_buat_pesanan_merender_modal_terbuka_secara_otomatis(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.create'));

        $response->assertStatus(200);
        $response->assertSee('id="modal-buat-pesanan"', false);
        $response->assertSee('data-auto-open="1"', false);
        $response->assertSee('data-standalone="1"', false);
        $response->assertSee('Buat Pesanan', false);
        $response->assertSee('form-order-customer', false);
    }

    public function test_validasi_gagal_menjaga_modal_tetap_terbuka_dengan_input_old(): void
    {
        $response = $this->actingAs($this->customer)
            ->from(route('customer.pesanan.create'))
            ->followingRedirects()
            ->post(route('customer.pesanan.store'), [
                'branch_id' => $this->branch->id,
                'notes' => 'Tolong pisahkan pakaian putih',
            ]);

        $response->assertStatus(200);
        $response->assertSee('data-auto-open="1"', false);
        $response->assertSee('Tolong pisahkan pakaian putih', false);
    }
}
