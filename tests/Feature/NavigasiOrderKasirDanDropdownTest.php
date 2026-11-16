<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigasiOrderKasirDanDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $manager;

    protected Branch $branch;

    protected Service $service;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Central Branch',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir Utama',
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Utama',
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
        ]);

        $category = ServiceCategory::create([
            'name' => 'Laundry Kiloan',
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'estimated_quantity' => 3.00,
            'status' => StatusPesanan::Received,
            'pickup_option' => false,
            'delivery_method' => 'pickup',
            'handover_status' => 'pending',
        ]);
    }

    /**
     * Test kasir mengakses /orders diarahkan ke /cashier/dashboard.
     */
    public function test_kasir_mengakses_orders_index_diarahkan_ke_cashier_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get('/orders');

        $response->assertRedirect(route('cashier.dashboard'));
    }

    /**
     * Test kasir mengakses /orders/create diarahkan ke /cashier/dashboard.
     */
    public function test_kasir_mengakses_orders_create_diarahkan_ke_cashier_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get('/orders/create');

        $response->assertRedirect(route('cashier.dashboard'));
    }

    /**
     * Test manager mengakses /orders dapat melihat halaman daftar order drop-off.
     */
    public function test_manager_dapat_mengakses_orders_index_secara_normal(): void
    {
        $response = $this->actingAs($this->manager)->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Order Drop-off');
    }

    /**
     * Test tombol Kembali pada Detail Order untuk kasir langsung menuju /cashier/dashboard.
     */
    public function test_tombol_kembali_pada_detail_order_kasir_mengarah_ke_cashier_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertSee(route('cashier.dashboard'));
        $response->assertDontSee(route('orders.index'));
    }

    /**
     * Test tombol Kembali pada Detail Order untuk manager tetap mengarah ke /orders.
     */
    public function test_tombol_kembali_pada_detail_order_manager_mengarah_ke_orders_index(): void
    {
        $response = $this->actingAs($this->manager)->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertSee(route('orders.index'));
    }

    /**
     * Test tampilan status pesanan kasir hanya read-only stepper tanpa dropdown ubah status.
     */
    public function test_detail_order_kasir_menampilkan_progres_tanpa_dropdown_status_manual(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertSee('Status Pesanan');
        $response->assertSee('Pembayaran & Penyerahan');
        $response->assertSee('Status diperbarui melalui tindakan operasional Kasir dan Karyawan.');
        $response->assertDontSee('order-status-select');
        $response->assertDontSee('btn-update-status-order');
    }

    /**
     * Test tampilan status pesanan manager tetap memiliki form status administratif.
     */
    public function test_detail_order_manager_memiliki_form_update_status_administratif(): void
    {
        $response = $this->actingAs($this->manager)->get(route('orders.show', $this->order));

        $response->assertStatus(200);
        $response->assertSee('order-status-select');
        $response->assertSee('btn-update-status-order');
    }

    /**
     * Test tabel pesanan yang perlu ditangani kasir menampilkan susunan kolom baru.
     */
    public function test_tabel_dashboard_kasir_menampilkan_susunan_kolom_baru(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pesanan');
        $response->assertSee('Pelanggan');
        $response->assertSee('Layanan');
        $response->assertSee('Est. Selesai');
        $response->assertSee('Penjemputan');
        $response->assertSee('Status');
        $response->assertSee('Tagihan');
        $response->assertSee('Aksi');
        $response->assertDontSee('>Jenis<', false);
    }

    /**
     * Test tabel kasir menampilkan estimasi selesai, penjemputan, dan tagihan estimasi dengan benar.
     */
    public function test_tabel_dashboard_kasir_menampilkan_data_penjemputan_dan_estimasi(): void
    {
        $this->order->update([
            'pickup_option' => true,
            'estimated_finish_at' => now()->addHours(6),
        ]);

        $response = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('#'.$this->order->id);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Cuci Kering Setrika');
        $response->assertSee('Ya');
        $response->assertSee('(Estimasi)');
        $response->assertSee('Timbang');
    }
}
