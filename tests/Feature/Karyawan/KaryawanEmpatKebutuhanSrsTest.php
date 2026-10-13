<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanEmpatKebutuhanSrsTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $otherEmployeeUser;

    protected Employee $otherEmployee;

    protected User $customerUser;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Sudirman 1',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Layanan Utama']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Hair Treatment',
            'price' => 100000,
            'service_type' => 'appointment',
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
            'status' => 'active',
        ]);

        $this->customerUser = User::create([
            'name' => 'Andi Pelanggan',
            'email' => 'andi@customer.local',
            'password' => Hash::make('password123'),
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_demo_employee_can_login_with_karyawan_email_and_access_dashboard(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'karyawan@simaul.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
        $this->assertAuthenticatedAs($this->employeeUser);
        $this->assertEquals('employee', $this->employeeUser->role->name);
        $this->assertEquals($this->branch->id, $this->employee->branch_id);

        $this->actingAs($this->employeeUser)
            ->get(route('employee.dashboard'))
            ->assertOk()
            ->assertSee('Dedi Kurniawan');
    }

    public function test_unauthorized_user_cannot_access_employee_dashboard(): void
    {
        $this->get(route('employee.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($this->customerUser)
            ->get(route('employee.dashboard'))
            ->assertForbidden();
    }

    public function test_employee_only_sees_assigned_today_tasks_and_not_other_employees_tasks(): void
    {
        $myBooking = $this->makeBooking($this->employee, '10:00');
        $otherBooking = $this->makeBooking($this->otherEmployee, '11:00');
        $myOrder = $this->makeOrder($this->employee);
        $otherOrder = $this->makeOrder($this->otherEmployee);

        $resDash = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'))->assertOk();
        $resDash->assertSee(route('bookings.show', $myBooking))->assertSee(route('orders.show', $myOrder))
            ->assertDontSee(route('bookings.show', $otherBooking))->assertDontSee(route('orders.show', $otherOrder));

        $resQueue = $this->actingAs($this->employeeUser)->get(route('queue.index'))->assertOk();
        $resQueue->assertSee(route('bookings.show', $myBooking))->assertSee(route('orders.show', $myOrder))
            ->assertDontSee(route('bookings.show', $otherBooking))->assertDontSee(route('orders.show', $otherOrder));
    }

    public function test_empty_state_rendered_when_no_tasks_today(): void
    {
        $this->actingAs($this->employeeUser)->get(route('employee.dashboard'))
            ->assertOk()->assertSee('Tidak ada tugas hari ini');
    }

    public function test_employee_can_update_task_status_through_valid_lifecycle(): void
    {
        $booking = $this->makeBooking($this->employee, '14:00');
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->employeeUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-TEST-01',
            'transaction_date' => now(),
            'subtotal' => 100000,
            'total' => 100000,
            'status' => StatusTransaksi::Pending,
        ]);

        $this->actingAs($this->employeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::InProgress->value])
            ->assertSessionHas('success');
        $this->assertEquals(BookingStatus::InProgress, $booking->fresh()->status);

        $this->actingAs($this->employeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::Completed->value])
            ->assertSessionHas('success');
        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);

        // Payment status must remain Pending (not changed to Paid just because task is completed)
        $this->assertEquals(StatusTransaksi::Pending, $tx->fresh()->status);

        $this->actingAs($this->customerUser)->get(route('customer.booking.show', $booking))
            ->assertOk()->assertSee('Selesai');
    }

    public function test_illegal_status_transition_is_rejected(): void
    {
        $booking = $this->makeBooking($this->employee, '14:00', BookingStatus::Completed);

        $this->actingAs($this->employeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::InProgress->value])
            ->assertSessionHas('error');
        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_other_employee_is_forbidden_from_modifying_task(): void
    {
        $booking = $this->makeBooking($this->employee, '14:00');

        $this->actingAs($this->otherEmployeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::InProgress->value])
            ->assertSessionHas('error');
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_employee_can_view_own_shift_and_cannot_view_other_employees_shift(): void
    {
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        EmployeeShift::create([
            'employee_id' => $this->otherEmployee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '21:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $res = $this->actingAs($this->employeeUser)->get(route('employees.my-shifts'))->assertOk();
        $res->assertSee('09:00 - 17:00')->assertSee('Cabang Pusat')->assertDontSee('13:00 - 21:00');
    }

    private function makeBooking(Employee $emp, string $time, BookingStatus $status = BookingStatus::Confirmed): Booking
    {
        return Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $emp->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => $time,
            'status' => $status,
        ]);
    }

    private function makeOrder(Employee $emp, StatusPesanan $status = StatusPesanan::Processing): Order
    {
        return Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $emp->id,
            'status' => $status,
        ]);
    }
}
