<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirDashboardMetricsDanSatuanTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $customer;

    protected Branch $branch;

    protected Service $bedCoverService;

    protected Service $kiloanService;

    protected function setUp(): void
    {
        parent::setUp();

        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care',
            'address' => 'Jl. Uji Kasir No. 2',
            'phone' => '081234567891',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'drop_off',
            'max_slot_capacity' => 10,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employeeUser = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Agus Santoso']);
        $this->employeeUser->branches()->attach($this->branch->id);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'phone' => '081234567881',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id, 'name' => 'Eko Prasetyo']);

        $categoryLaundry = ServiceCategory::firstOrCreate(
            ['name' => 'Laundry Kiloan & Reguler'],
            ['description' => 'Kategori laundry kiloan dan satuan']
        );

        $this->bedCoverService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $categoryLaundry->id,
            'name' => 'Cuci Bed Cover',
            'price' => 35000,
            'unit' => 'item',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $this->kiloanService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $categoryLaundry->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 9000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);
    }

    public function test_layanan_cuci_bed_cover_dengan_satuan_item_bukan_weight_based(): void
    {
        $this->assertFalse($this->bedCoverService->isWeightBased());
        $this->assertTrue($this->kiloanService->isWeightBased());
    }

    public function test_kasir_menerima_pesanan_cuci_bed_cover_mengubah_tombol_menjadi_lihat_bukan_timbang(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->bedCoverService->id,
            'status' => StatusPesanan::Pending,
            'estimated_quantity' => 2.0,
            'total_amount' => 70000,
        ]);

        $responseReceive = $this->actingAs($this->cashier)
            ->postJson(route('pos.orders.receive', $order->id), [
                'employee_id' => $this->employee->id,
            ]);

        $responseReceive->assertOk()
            ->assertJson([
                'success' => true,
                'is_weight_based' => false,
            ]);

        $order->refresh();
        $this->assertEquals(StatusPesanan::Received, $order->status);
        $this->assertEquals($this->employee->id, $order->employee_id);

        $responseDashboard = $this->actingAs($this->cashier)
            ->get(route('cashier.dashboard', ['branch_id' => $this->branch->id]));

        $responseDashboard->assertOk()
            ->assertSee(route('orders.show', $order->id), false)
            ->assertSee('Lihat', false)
            ->assertDontSee('data-action="open-weight-modal"', false);
    }

    public function test_pesanan_cuci_bed_cover_masuk_ke_tugas_karyawan_terpilih(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->bedCoverService->id,
            'status' => StatusPesanan::Received,
            'employee_id' => $this->employee->id,
            'estimated_quantity' => 2.0,
            'total_amount' => 70000,
        ]);

        $responseEmployee = $this->actingAs($this->employeeUser)
            ->get(route('employee.dashboard'));

        $responseEmployee->assertOk()
            ->assertSee('Cuci Bed Cover', false)
            ->assertSee('Eko Prasetyo', false)
            ->assertSee('Mulai Pengerjaan', false);
    }

    public function test_kartu_statistik_transaksi_hari_ini_menampilkan_label_dan_deskripsi_baru(): void
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.dashboard', ['branch_id' => $this->branch->id]));

        $response->assertOk()
            ->assertSee('Total transaksi hari ini', false)
            ->assertSee('Transaksi', false)
            ->assertDontSee('Transaksi penjualan kasir', false);
    }
}
