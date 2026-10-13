<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanUxDanBatasanShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $managerUser;

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

        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Merdeka No. 10',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->managerUser = User::create([
            'name' => 'Budi Manager',
            'email' => 'manager.pusat@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $managerRole->id,
        ]);
        $this->managerUser->branches()->attach($this->branch->id);

        $category = ServiceCategory::create(['name' => 'Perawatan Rambut']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Gentleman Haircut',
            'price' => 75000,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->employeeUser = User::create([
            'name' => 'Dedi Barber',
            'email' => 'dedi.barber@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Barber',
            'status' => 'active',
        ]);

        $this->otherEmployeeUser = User::create([
            'name' => 'Rian Barber',
            'email' => 'rian.barber@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->otherEmployee = Employee::create([
            'user_id' => $this->otherEmployeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Rian Barber',
            'status' => 'active',
        ]);

        $this->customerUser = User::create([
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_queue_page_displays_essential_task_info_and_clean_business_name(): void
    {
        $myBooking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $myOrder = Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Processing,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('queue.index'));

        $response->assertOk();
        $response->assertSee('<title>Antrean &amp; Tugas | SIMAUL</title>', false);
        $response->assertSee('Antrean &amp; Tugas', false);
        $response->assertSee('10:00');
        $response->assertSee('Gentleman Haircut');
        $response->assertSee('Barbershop & Studio');
        $response->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $response->assertSee(route('bookings.show', $myBooking));
        $response->assertSee(route('orders.show', $myOrder));
    }

    public function test_queue_empty_state_shows_clear_message(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('queue.index'));

        $response->assertOk();
        $response->assertSee('Belum ada tugas yang ditugaskan kepada Anda hari ini.');
        $response->assertDontSee('Tidak ada tugas appointment hari ini');
        $response->assertDontSee('Tidak ada order drop-off hari ini');
    }

    public function test_employee_can_view_detail_tugas_and_update_status(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('bookings.show', $booking));

        $response->assertOk();
        $response->assertSee('<title>Detail Tugas | SIMAUL</title>', false);
        $response->assertSee('Detail Tugas');
        $response->assertDontSee('Referensi #'.$booking->id);
        $response->assertSee('Informasi Tugas');
        $response->assertSee('Pelanggan Setia');
        $response->assertSee('Barbershop & Studio');
        $response->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $response->assertDontSee('Edit Booking');
        $response->assertDontSee('Batalkan Booking');

        // Status update
        $updateRes = $this->actingAs($this->employeeUser)
            ->patch(route('bookings.update-status', $booking), ['status' => BookingStatus::InProgress->value]);

        $updateRes->assertSessionHas('success');
        $this->assertEquals(BookingStatus::InProgress, $booking->fresh()->status);
    }

    public function test_employee_cannot_access_administrative_booking_edit(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $this->actingAs($this->employeeUser)
            ->get(route('bookings.edit', $booking))
            ->assertForbidden();
    }

    public function test_shift_duration_exceeding_eight_hours_is_rejected(): void
    {
        // 9 hours: 08:00 to 17:00 -> must fail
        $response = $this->actingAs($this->managerUser)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertSessionHasErrors('end_time');
        $this->assertDatabaseMissing('employee_shifts', [
            'employee_id' => $this->employee->id,
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);
    }

    public function test_shift_duration_under_or_equal_eight_hours_is_accepted(): void
    {
        // Exactly 8 hours: 08:00 to 16:00 -> must succeed
        $response = $this->actingAs($this->managerUser)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_shifts', [
            'employee_id' => $this->employee->id,
            'start_time' => '08:00',
            'end_time' => '16:00',
        ]);
    }

    public function test_shift_update_duration_exceeding_eight_hours_is_rejected(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->managerUser)->put(route('employees.shifts.update', $shift), [
            'start_time' => '08:00',
            'end_time' => '17:30', // 9.5 hours
        ]);

        $response->assertSessionHasErrors('end_time');
        $this->assertStringStartsWith('16:00', (string) $shift->fresh()->end_time);
    }

    public function test_browser_titles_and_headings_are_consistent_across_employee_pages(): void
    {
        // 1. Dashboard
        $dashRes = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $dashRes->assertOk();
        $dashRes->assertSee('<title>Dashboard Karyawan | SIMAUL</title>', false);
        $dashRes->assertSee('Dashboard');

        // 2. Antrean & Tugas
        $queueRes = $this->actingAs($this->employeeUser)->get(route('queue.index'));
        $queueRes->assertOk();
        $queueRes->assertSee('<title>Antrean &amp; Tugas | SIMAUL</title>', false);
        $queueRes->assertSee('Antrean &amp; Tugas', false);

        // 3. Jadwal Shift
        $shiftRes = $this->actingAs($this->employeeUser)->get(route('employees.my-shifts'));
        $shiftRes->assertOk();
        $shiftRes->assertSee('<title>Jadwal Shift | SIMAUL</title>', false);
        $shiftRes->assertSee('Jadwal Shift');

        // 4. Komisi Saya
        $commRes = $this->actingAs($this->employeeUser)->get(route('employees.my-commissions'));
        $commRes->assertOk();
        $commRes->assertSee('<title>Komisi Saya | SIMAUL</title>', false);
        $commRes->assertSee('Komisi Saya');
    }
}
