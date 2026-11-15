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

class PenugasanKaryawanOlehKasirTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $employeeUser1;

    protected Employee $employee1;

    protected User $employeeUser2;

    protected Employee $employee2;

    protected User $customer;

    protected Branch $branch;

    protected Service $kiloanService;

    protected Service $nonKiloanService;

    protected function setUp(): void
    {
        parent::setUp();

        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care',
            'address' => 'Jl. Uji Penugasan No. 1',
            'phone' => '081234567890',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'drop_off',
            'max_slot_capacity' => 10,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employeeUser1 = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Agus Santoso']);
        $this->employeeUser1->branches()->attach($this->branch->id);
        $this->employee1 = Employee::create([
            'user_id' => $this->employeeUser1->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'phone' => '081234567881',
            'status' => 'active',
        ]);

        $this->employeeUser2 = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Budi Pratama']);
        $this->employeeUser2->branches()->attach($this->branch->id);
        $this->employee2 = Employee::create([
            'user_id' => $this->employeeUser2->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Pratama',
            'phone' => '081234567882',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id, 'phone' => '081234567899']);

        $category = ServiceCategory::firstOrCreate(
            ['name' => 'Perawatan Sepatu'],
            ['description' => 'Kategori sepatu']
        );

        $this->kiloanService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Kiloan Reguler',
            'price' => 8000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $this->nonKiloanService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Deep Clean Shoes',
            'price' => 35000,
            'unit' => 'pasang',
            'service_type' => 'drop_off',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);
    }

    public function test_kasir_dapat_mengambil_daftar_karyawan_aktif_cabang(): void
    {
        $response = $this->actingAs($this->cashier)
            ->getJson(route('pos.branch.employees', $this->branch->id));

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Agus Santoso'])
            ->assertJsonFragment(['name' => 'Budi Pratama']);
    }

    public function test_kasir_dapat_menerima_pesanan_non_kiloan_dan_menugaskan_karyawan(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->nonKiloanService->id,
            'status' => StatusPesanan::Pending,
            'total_amount' => 35000,
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.orders.receive', $order->id), [
                'employee_id' => $this->employee1->id,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(StatusPesanan::Received, $order->fresh()->status);
        $this->assertEquals($this->employee1->id, $order->fresh()->employee_id);
    }

    public function test_kasir_dapat_menerima_pesanan_kiloan(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->kiloanService->id,
            'status' => StatusPesanan::Pending,
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.orders.receive', $order->id));

        $response->assertOk()
            ->assertJson(['success' => true, 'is_weight_based' => true]);

        $this->assertEquals(StatusPesanan::Received, $order->fresh()->status);
    }

    public function test_kasir_dapat_menimbang_laundry_dan_menugaskan_karyawan(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->kiloanService->id,
            'status' => StatusPesanan::Received,
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.orders.weigh', $order->id), [
                'actual_weight' => 4.5,
                'employee_id' => $this->employee2->id,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(4.5, (float) $order->actual_weight);
        $this->assertEquals(36000, (float) $order->total_amount);
        $this->assertEquals($this->employee2->id, $order->employee_id);
    }

    public function test_teks_tombol_pesanan_pelanggan_adalah_pesan_dan_pesan_sekarang(): void
    {
        $responseCreate = $this->actingAs($this->customer)
            ->get(route('customer.pesanan.create'));

        $responseCreate->assertOk()
            ->assertSee('Pesan')
            ->assertDontSee('Lihat Estimasi & Konfirmasi');
    }
}
