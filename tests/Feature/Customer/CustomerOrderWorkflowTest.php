<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected Branch $branchPusat;

    protected Branch $branchSelatan;

    protected Branch $branchBarat;

    protected Service $laundryService;

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

        $this->otherCustomer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
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
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 1440,
            'is_active' => true,
        ]);

        $autoCategory = ServiceCategory::create(['name' => 'Cuci Mobil & Interior']);
        $this->autoCareService = Service::create([
            'service_category_id' => $autoCategory->id,
            'branch_id' => $this->branchBarat->id,
            'name' => 'Cuci Mobil Premium',
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
            'price' => 20000,
            'unit' => 'item',
            'service_type' => 'drop_off',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
    }

    public function test_opening_form_tanpa_cabang_tidak_menampilkan_layanan(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.create'));
        $response->assertStatus(200);
        $response->assertSee('Pilih Cabang Terlebih Dahulu');
        $response->assertDontSee('Potong Rambut Pria');
    }

    public function test_opening_form_with_mismatched_branch_and_service_resets_service(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchBarat->id,
            'service_id' => $this->barbershopService->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Buat Pesanan');
        $response->assertDontSee('selected value="'.$this->barbershopService->id.'"');
        $response->assertSee('Cuci Mobil Premium');
    }

    public function test_service_dari_cabang_lain_tidak_bisa_dikonfirmasi(): void
    {
        $responsePusat = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->laundryService->id,
        ]);
        $responsePusat->assertSessionHasErrors('service_id');
    }

    public function test_service_dari_cabang_lain_tidak_bisa_disimpan_secara_manual(): void
    {
        $responsePusat = $this->actingAs($this->customer)->post(route('customer.pesanan.store'), [
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->laundryService->id,
        ]);
        $responsePusat->assertSessionHasErrors('service_id');
    }

    public function test_service_format_estimated_duration_helper_rules(): void
    {
        $this->assertEquals('±1 jam', Service::formatEstimatedDuration(45));
        $this->assertEquals('±1 jam', Service::formatEstimatedDuration(60));
        $this->assertEquals('±1,5 jam', Service::formatEstimatedDuration(90));
        $this->assertEquals('±1 hari', Service::formatEstimatedDuration(1440));
    }

    public function test_durasi_customer_tampil_dalam_format_jam_dan_hari_bukan_raw_menit(): void
    {
        $responseBarber = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->barbershopService->id,
        ]));
        $responseBarber->assertStatus(200);
        $responseBarber->assertSee('±1 jam');

        $confirmResponse = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
        ]);
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee('±1 hari');
    }

    public function test_laundry_service_form_shows_weight_info_and_optional_bag_field(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Berat Cucian');
    }

    public function test_customer_can_confirm_weight_based_laundry_order_without_fake_total(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'estimated_quantity' => 2,
            'pickup_option' => 1,
            'notes' => 'Tolong pisahkan pakaian putih',
        ]);
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Pesanan');
        $response->assertSee('Tarif Layanan');
        $response->assertSee('Rp 10.000 / kg');
    }

    public function test_customer_can_store_laundry_order_without_knowing_weight(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.store'), [
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'estimated_quantity' => null,
            'pickup_option' => 1,
            'notes' => 'Cucian kemeja kantor',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Pending->value,
        ]);

        $response->assertRedirect(route('customer.pesanan.detail'));
    }

    public function test_customer_can_confirm_and_store_fixed_price_service_without_quantity(): void
    {
        $confirmResponse = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->barbershopService->id,
            'pickup_option' => 0,
            'notes' => 'Potong gaya undercut',
        ]);
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee('Konfirmasi Pesanan');
        $confirmResponse->assertSee('Rp 95.000');

        $storeResponse = $this->actingAs($this->customer)->post(route('customer.pesanan.store'), [
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->barbershopService->id,
            'pickup_option' => 0,
            'notes' => 'Potong gaya undercut',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchPusat->id,
            'service_id' => $this->barbershopService->id,
            'status' => StatusPesanan::Pending->value,
        ]);

        $storeResponse->assertRedirect(route('customer.pesanan.detail'));
    }

    public function test_customer_can_view_my_orders(): void
    {
        Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'estimated_quantity' => 2.5,
            'status' => StatusPesanan::Processing,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.index'));
        $response->assertStatus(200);
        $response->assertSee('Pesanan Saya');
        $response->assertSee('Cuci Kering Setrika');
    }

    public function test_customer_cannot_view_other_customers_order(): void
    {
        $otherOrder = Order::create([
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Pending,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.show', $otherOrder));
        $response->assertStatus(403);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchSelatan->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Pending,
        ]);

        $response = $this->actingAs($this->customer)->patch(route('customer.pesanan.cancel', $order));
        $response->assertRedirect(route('customer.pesanan.index'));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => StatusPesanan::Cancelled->value,
        ]);
    }
}
