<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeShift;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleKaryawanOtorisasiDanBatasanSrsTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $otherEmployeeUser;

    protected Employee $otherEmployee;

    protected User $ownerUser;

    protected User $customerUser;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'Barbershop & Studio',
            'address' => 'Jl. Sudirman No. 10',
            'phone' => '0811223344',
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Hair Treatment']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Executive Cut',
            'price' => 100000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $this->employeeUser = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $this->otherEmployeeUser = User::create([
            'name' => 'Agus Santoso',
            'email' => 'agus@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->otherEmployee = Employee::create([
            'user_id' => $this->otherEmployeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        $this->ownerUser = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $ownerRole->id,
        ]);

        $this->customerUser = User::create([
            'name' => 'Andi Pelanggan',
            'email' => 'andi@customer.local',
            'password' => Hash::make('password123'),
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_employee_cannot_create_or_edit_administrative_bookings(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $this->actingAs($this->employeeUser)->get(route('bookings.create'))->assertForbidden();
        $this->actingAs($this->employeeUser)->post(route('bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '11:00',
        ])->assertForbidden();

        $this->actingAs($this->employeeUser)->get(route('bookings.edit', $booking))->assertForbidden();
        $this->actingAs($this->employeeUser)->put(route('bookings.update', $booking), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'booking_time' => '12:00',
        ])->assertForbidden();

        $this->actingAs($this->employeeUser)->patch(route('bookings.cancel', $booking))->assertForbidden();
    }

    public function test_employee_cannot_create_or_cancel_dropoff_orders(): void
    {
        $order = Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Pending,
        ]);

        $this->actingAs($this->employeeUser)->get(route('orders.create'))->assertForbidden();
        $this->actingAs($this->employeeUser)->post(route('orders.store'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
        ])->assertForbidden();

        $this->actingAs($this->employeeUser)->patch(route('orders.cancel', $order))->assertForbidden();
    }

    public function test_employee_cannot_manage_shifts_or_commissions(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $this->actingAs($this->employeeUser)->get(route('employees.shifts.index'))->assertForbidden();
        $this->actingAs($this->employeeUser)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '18:00',
        ])->assertForbidden();
        $this->actingAs($this->employeeUser)->put(route('employees.shifts.update', $shift), [
            'start_time' => '08:00',
        ])->assertForbidden();
        $this->actingAs($this->employeeUser)->delete(route('employees.shifts.destroy', $shift))->assertForbidden();

        $commission = EmployeeCommission::create([
            'employee_id' => $this->employee->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'commission_amount' => 10000,
            'status' => CommissionStatus::Pending->value,
        ]);

        $this->actingAs($this->employeeUser)->get(route('employees.commissions.index'))->assertForbidden();
        $this->actingAs($this->employeeUser)->post(route('employees.commissions.store'), [
            'employee_id' => $this->employee->id,
            'service_id' => $this->service->id,
            'commission_type' => 'fixed',
            'commission_value' => 15000,
        ])->assertForbidden();
        $this->actingAs($this->employeeUser)->patch(route('employees.commissions.status', $commission), [
            'status' => CommissionStatus::Approved->value,
        ])->assertForbidden();
    }

    public function test_employee_can_update_own_dropoff_order_and_cannot_update_other_order(): void
    {
        $myOrder = Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Received,
        ]);

        $otherOrder = Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->otherEmployee->id,
            'status' => StatusPesanan::Received,
        ]);

        $this->actingAs($this->employeeUser)
            ->patch(route('orders.update-status', $myOrder), ['status' => StatusPesanan::Processing->value])
            ->assertSessionHas('success');
        $this->assertEquals(StatusPesanan::Processing, $myOrder->fresh()->status);

        $this->actingAs($this->employeeUser)
            ->patch(route('orders.update-status', $myOrder), ['status' => StatusPesanan::Completed->value])
            ->assertSessionHas('success');
        $this->assertEquals(StatusPesanan::Completed, $myOrder->fresh()->status);

        $this->actingAs($this->employeeUser)
            ->patch(route('orders.update-status', $otherOrder), ['status' => StatusPesanan::Processing->value])
            ->assertForbidden();
        $this->assertEquals(StatusPesanan::Received, $otherOrder->fresh()->status);
    }

    public function test_commission_automation_lifecycle_and_non_duplication(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::InProgress,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->ownerUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-AUTO-COMM-01',
            'transaction_date' => now(),
            'subtotal' => 100000,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
        ]);

        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $this->service->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);

        $this->assertDatabaseMissing('employee_commissions', [
            'transaction_id' => $tx->id,
        ]);

        $this->actingAs($this->employeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::Completed->value])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employee_commissions', [
            'transaction_id' => $tx->id,
            'employee_id' => $this->employee->id,
            'commission_amount' => 10000,
            'status' => CommissionStatus::Pending->value,
        ]);
        $this->assertEquals(1, EmployeeCommission::where('transaction_id', $tx->id)->count());

        $this->actingAs($this->ownerUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::Completed->value]);
        $this->assertEquals(1, EmployeeCommission::where('transaction_id', $tx->id)->count());
    }
}
