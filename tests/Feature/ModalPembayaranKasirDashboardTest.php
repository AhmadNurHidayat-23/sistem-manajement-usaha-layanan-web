<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalPembayaranKasirDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierUser;

    protected User $employeeUser;

    protected Employee $employee;

    protected Branch $branch;

    protected Service $service;

    protected CashierShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $roleOwner = Role::firstOrCreate(['name' => 'owner'], ['display_name' => 'Owner']);
        $roleCashier = Role::firstOrCreate(['name' => 'cashier'], ['display_name' => 'Kasir']);
        $roleEmployee = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Karyawan']);

        $this->branch = Branch::create([
            'name' => 'Cabang Testing',
            'address' => 'Jl. Testing No. 1',
            'phone' => '081234567890',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->cashierUser = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir.test@simaul.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCashier->id,
            'branch_id' => $this->branch->id,
        ]);
        $this->cashierUser->branches()->attach($this->branch->id);

        $this->employeeUser = User::create([
            'name' => 'Karyawan Test',
            'email' => 'karyawan.test@simaul.com',
            'password' => bcrypt('password'),
            'role_id' => $roleEmployee->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Karyawan Test',
            'status' => 'active',
        ]);

        $category = ServiceCategory::create([
            'name' => 'Laundry Kiloan',
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Kering Express',
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'price' => 50000,
            'service_type' => 'both',
            'is_active' => true,
        ]);

        $this->shift = CashierShift::create([
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branch->id,
            'starting_cash' => 200000,
            'opened_at' => now(),
            'status' => 'open',
        ]);
    }

    public function test_dashboard_karyawan_menggunakan_modal_kustom_dan_tidak_menggunakan_confirm_native_browser(): void
    {
        Booking::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));

        $response->assertOk();
        // Pastikan tidak ada dialog confirm() bawaan browser
        $response->assertDontSee('onsubmit="return confirm(');
        // Pastikan modal kustom tugas disertakan
        $response->assertSee('id="modal-konfirmasi-tugas"', false);
        $response->assertSee('openTaskConfirmModal');
    }

    public function test_dashboard_kasir_menampilkan_tombol_bayar_dan_modal_pembayaran_langsung(): void
    {
        Booking::create([
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->cashierUser)->get(route('cashier.dashboard'));

        $response->assertOk();
        // Tombol Bayar di POS diubah menjadi Bayar
        $response->assertDontSee('Bayar di POS');
        $response->assertSee('Bayar');
        $response->assertSee('id="modal-pembayaran-kasir"', false);
        $response->assertSee('data-action="open-payment-modal"', false);
    }

    public function test_kasir_dapat_memproses_pembayaran_langsung_via_modal_dengan_respons_json(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081299887766',
            'payment_method' => 'cash',
            'cash_received' => 100000,
            'items' => [
                [
                    'service_id' => $this->service->id,
                    'qty' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashierUser)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('pos.transactions.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_gateway' => false,
            'transaction' => [
                'total' => 50000,
                'status' => StatusTransaksi::Paid->value,
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'branch_id' => $this->branch->id,
            'status' => StatusTransaksi::Paid->value,
            'total' => 50000,
        ]);
    }

    public function test_modal_pembayaran_menolak_jika_uang_tunai_kurang_dari_total(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'customer_name' => 'Budi Santoso',
            'payment_method' => 'cash',
            'cash_received' => 20000, // Tagihan 50000
            'items' => [
                [
                    'service_id' => $this->service->id,
                    'qty' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashierUser)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('pos.transactions.store'), $payload);

        $response->assertStatus(422);
    }
}
