<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\Karyawan\KomisiKaryawanService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanKomisiOtomatisSrsTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $otherEmployeeUser;

    protected Employee $otherEmployee;

    protected User $cashierUser;

    protected Branch $branch;

    protected ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka 10',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->category = ServiceCategory::create(['name' => 'Salon']);

        $this->employeeUser = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
            'password' => bcrypt('password123'),
            'role_id' => $employeeRole->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'status' => 'active',
        ]);

        $this->otherEmployeeUser = User::create([
            'name' => 'Agus Santoso',
            'email' => 'agus@simaul.com',
            'password' => bcrypt('password123'),
            'role_id' => $employeeRole->id,
        ]);

        $this->otherEmployee = Employee::create([
            'user_id' => $this->otherEmployeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'status' => 'active',
        ]);

        $this->cashierUser = User::create([
            'name' => 'Kasir Andi',
            'email' => 'cashier@simaul.com',
            'password' => bcrypt('password123'),
            'role_id' => $cashierRole->id,
        ]);
        $this->cashierUser->branches()->attach($this->branch->id);
    }

    public function test_automatic_commission_calculated_from_service_config_and_viewable_only_by_assigned_employee(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Smoothing Rambut',
            'price' => 200000,
            'service_type' => 'appointment',
            'is_active' => true,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10.00,
        ]);

        $booking = Booking::create([
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Completed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashierUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-COMM-01',
            'transaction_date' => now(),
            'subtotal' => 200000,
            'total' => 200000,
            'status' => StatusTransaksi::Paid,
        ]);

        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $service->id,
            'qty' => 1,
            'price' => 200000,
            'subtotal' => 200000,
        ]);

        $created = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(1, $created);
        $this->assertEquals(20000.00, (float) $created->first()->commission_amount);
        $this->assertEquals(CommissionStatus::Pending, $created->first()->status);

        // Employee sees own commission on /my-commissions
        $resSelf = $this->actingAs($this->employeeUser)->get(route('employees.my-commissions'));
        $resSelf->assertOk();
        $resSelf->assertSee('Smoothing Rambut');
        $resSelf->assertSee('20.000');

        // Other employee does not see this commission
        $resOther = $this->actingAs($this->otherEmployeeUser)->get(route('employees.my-commissions'));
        $resOther->assertOk();
        $resOther->assertDontSee('Smoothing Rambut');
    }

    public function test_repeated_processing_is_strictly_idempotent_no_duplicate_commission(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Haircut Biasa',
            'price' => 50000,
            'service_type' => 'appointment',
            'is_active' => true,
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 15000.00,
        ]);

        $booking = Booking::create([
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Completed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashierUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-IDEMP-01',
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
        ]);

        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $service->id,
            'qty' => 1,
            'price' => 50000,
            'subtotal' => 50000,
        ]);

        // First call
        $first = app(KomisiKaryawanService::class)->processTransactionCommission($tx);
        $this->assertCount(1, $first);

        // Second call (e.g. repeated webhook or status save)
        $second = app(KomisiKaryawanService::class)->processTransactionCommission($tx);
        $this->assertCount(0, $second);

        $this->assertEquals(1, EmployeeCommission::where('transaction_id', $tx->id)->count());
    }

    public function test_cashier_never_receives_commission_and_unassigned_transaction_is_skipped(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Lipat',
            'price' => 30000,
            'service_type' => 'drop_off',
            'is_active' => true,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 20.00,
        ]);

        // Transaction without employee assignment
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashierUser->id,
            'employee_id' => null,
            'booking_id' => null,
            'order_id' => null,
            'transaction_code' => 'TRX-NO-EMP',
            'transaction_date' => now(),
            'subtotal' => 30000,
            'total' => 30000,
            'status' => StatusTransaksi::Paid,
        ]);

        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $service->id,
            'qty' => 1,
            'price' => 30000,
            'subtotal' => 30000,
        ]);

        $res = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(0, $res);
        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);
    }
}
