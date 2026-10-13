<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HakAksesEditBookingKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $managerBranch1;

    protected User $managerBranch2;

    protected User $employeeUser1;

    protected Employee $employee1;

    protected User $employeeUser2;

    protected Employee $employee2;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Service $service1;

    protected Service $service2;

    protected Booking $bookingEmployee1;

    protected Booking $bookingEmployee2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $getRole = fn (UserRole $r) => Role::where('name', $r->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Pusat', 'address' => 'Jl. Pusat No. 1', 'phone' => '0811111111',
            'opening_time' => '08:00', 'closing_time' => '17:00', 'service_mode' => 'both',
            'max_slot_capacity' => 5, 'is_active' => true,
        ]);
        $this->branch2 = Branch::create([
            'name' => 'Cabang Timur', 'address' => 'Jl. Timur No. 2', 'phone' => '0822222222',
            'opening_time' => '08:00', 'closing_time' => '17:00', 'service_mode' => 'both',
            'max_slot_capacity' => 5, 'is_active' => true,
        ]);

        $cat = ServiceCategory::create(['name' => 'Perawatan', 'description' => 'Layanan']);
        $this->service1 = Service::create([
            'service_category_id' => $cat->id, 'branch_id' => $this->branch1->id, 'name' => 'Layanan Standar',
            'price' => 50000, 'estimated_duration' => 60, 'service_type' => 'appointment', 'is_active' => true,
        ]);
        $this->service2 = Service::create([
            'service_category_id' => $cat->id, 'branch_id' => $this->branch1->id, 'name' => 'Layanan Spesial',
            'price' => 100000, 'estimated_duration' => 90, 'service_type' => 'appointment', 'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['role_id' => $getRole(UserRole::Owner)->id]);
        $this->managerBranch1 = User::factory()->create(['role_id' => $getRole(UserRole::Manager)->id]);
        $this->managerBranch1->branches()->attach($this->branch1->id);
        $this->managerBranch2 = User::factory()->create(['role_id' => $getRole(UserRole::Manager)->id]);
        $this->managerBranch2->branches()->attach($this->branch2->id);

        $this->employeeUser1 = User::factory()->create(['role_id' => $getRole(UserRole::Employee)->id]);
        $this->employee1 = Employee::create([
            'user_id' => $this->employeeUser1->id, 'branch_id' => $this->branch1->id,
            'name' => 'Dedi Kurniawan', 'phone' => '0812345678', 'status' => 'active',
        ]);
        $this->employeeUser2 = User::factory()->create(['role_id' => $getRole(UserRole::Employee)->id]);
        $this->employee2 = Employee::create([
            'user_id' => $this->employeeUser2->id, 'branch_id' => $this->branch1->id,
            'name' => 'Siti Staff', 'phone' => '0898765432', 'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $getRole(UserRole::Customer)->id]);

        $this->bookingEmployee1 = Booking::create([
            'user_id' => $this->customer->id, 'branch_id' => $this->branch1->id, 'service_id' => $this->service1->id,
            'employee_id' => $this->employee1->id, 'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00', 'notes' => 'Catatan awal', 'status' => BookingStatus::Confirmed,
        ]);
        $this->bookingEmployee2 = Booking::create([
            'user_id' => $this->customer->id, 'branch_id' => $this->branch1->id, 'service_id' => $this->service1->id,
            'employee_id' => $this->employee2->id, 'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00', 'notes' => 'Tugas Siti', 'status' => BookingStatus::Confirmed,
        ]);

        foreach (range(1, 5) as $dayOffset) {
            foreach ([$this->employee1, $this->employee2] as $emp) {
                EmployeeShift::create([
                    'employee_id' => $emp->id, 'branch_id' => $this->branch1->id,
                    'shift_date' => now()->addDays($dayOffset)->toDateString(),
                    'start_time' => '08:00', 'end_time' => '17:00', 'status' => StatusShiftKaryawan::Scheduled,
                ]);
            }
        }
    }

    public function test_employee_cannot_open_edit_booking_page(): void
    {
        $this->actingAs($this->employeeUser1)
            ->get(route('bookings.edit', $this->bookingEmployee1))
            ->assertForbidden();
    }

    public function test_employee_cannot_send_put_to_update_booking_details(): void
    {
        $payload = [
            'branch_id' => $this->branch1->id, 'service_id' => $this->service1->id,
            'employee_id' => $this->employee1->id, 'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '14:00', 'notes' => 'Diubah karyawan',
        ];

        $this->actingAs($this->employeeUser1)
            ->put(route('bookings.update', $this->bookingEmployee1), $payload)
            ->assertForbidden();

        $this->assertEquals('10:00', substr((string) $this->bookingEmployee1->fresh()->booking_time, 0, 5));
        $this->assertEquals('Catatan awal', $this->bookingEmployee1->fresh()->notes);
    }

    public function test_employee_cannot_change_booking_branch_service_date_time_or_employee(): void
    {
        $orig = $this->bookingEmployee1->fresh();

        $this->actingAs($this->employeeUser1)->put(route('bookings.update', $this->bookingEmployee1), [
            'branch_id' => $this->branch2->id, 'booking_date' => $orig->booking_date->toDateString(), 'booking_time' => '10:00',
        ])->assertForbidden();
        $this->assertEquals($orig->branch_id, $this->bookingEmployee1->fresh()->branch_id);

        $this->actingAs($this->employeeUser1)->put(route('bookings.update', $this->bookingEmployee1), [
            'service_id' => $this->service2->id, 'booking_date' => $orig->booking_date->toDateString(), 'booking_time' => '10:00',
        ])->assertForbidden();
        $this->assertEquals($orig->service_id, $this->bookingEmployee1->fresh()->service_id);

        $this->actingAs($this->employeeUser1)->put(route('bookings.update', $this->bookingEmployee1), [
            'booking_date' => now()->addDays(5)->toDateString(), 'booking_time' => '15:00',
        ])->assertForbidden();
        $this->assertEquals($orig->booking_date->toDateString(), $this->bookingEmployee1->fresh()->booking_date->toDateString());

        $this->actingAs($this->employeeUser1)->put(route('bookings.update', $this->bookingEmployee1), [
            'employee_id' => $this->employee2->id, 'booking_date' => $orig->booking_date->toDateString(), 'booking_time' => '10:00',
        ])->assertForbidden();
        $this->assertEquals($this->employee1->id, $this->bookingEmployee1->fresh()->employee_id);
    }

    public function test_employee_can_view_own_task_but_cannot_view_other_employee_task(): void
    {
        $this->actingAs($this->employeeUser1)
            ->get(route('bookings.show', $this->bookingEmployee1))
            ->assertOk()
            ->assertDontSee('Edit Booking')
            ->assertDontSee('Batalkan Booking');

        $this->actingAs($this->employeeUser1)
            ->get(route('bookings.show', $this->bookingEmployee2))
            ->assertForbidden();
    }

    public function test_employee_can_update_status_of_assigned_task(): void
    {
        $this->actingAs($this->employeeUser1)
            ->patch(route('bookings.update-status', $this->bookingEmployee1), [
                'status' => BookingStatus::InProgress->value,
            ])
            ->assertSessionHas('success');

        $this->assertEquals(BookingStatus::InProgress, $this->bookingEmployee1->fresh()->status);
    }

    public function test_employee_cannot_update_status_of_other_employee_task(): void
    {
        $this->actingAs($this->employeeUser1)
            ->patch(route('bookings.update-status', $this->bookingEmployee2), [
                'status' => BookingStatus::InProgress->value,
            ])
            ->assertSessionHas('error');

        $this->assertEquals(BookingStatus::Confirmed, $this->bookingEmployee2->fresh()->status);
    }

    public function test_authorized_owner_and_branch_manager_can_manage_bookings(): void
    {
        $targetDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->owner)
            ->get(route('bookings.edit', $this->bookingEmployee1))->assertOk();

        $this->actingAs($this->owner)
            ->put(route('bookings.update', $this->bookingEmployee1), [
                'branch_id' => $this->branch1->id, 'service_id' => $this->service1->id,
                'employee_id' => $this->employee1->id, 'booking_date' => $targetDate,
                'booking_time' => '13:00', 'notes' => 'Updated by owner',
            ])
            ->assertRedirect(route('bookings.show', $this->bookingEmployee1));

        $this->assertEquals('13:00', substr((string) $this->bookingEmployee1->fresh()->booking_time, 0, 5));

        $this->actingAs($this->managerBranch1)
            ->get(route('bookings.edit', $this->bookingEmployee1))->assertOk();

        $this->actingAs($this->managerBranch2)
            ->get(route('bookings.edit', $this->bookingEmployee1))->assertForbidden();
    }

    public function test_customer_can_manage_own_appointment(): void
    {
        $targetDate = now()->addDays(4)->toDateString();

        $this->actingAs($this->customer)
            ->get(route('bookings.show', $this->bookingEmployee1))->assertOk();

        $this->actingAs($this->customer)
            ->get(route('bookings.edit', $this->bookingEmployee1))->assertOk();

        $this->actingAs($this->customer)
            ->put(route('bookings.update', $this->bookingEmployee1), [
                'branch_id' => $this->branch1->id, 'service_id' => $this->service1->id,
                'employee_id' => $this->employee1->id, 'booking_date' => $targetDate,
                'booking_time' => '14:00',
            ])
            ->assertRedirect(route('bookings.show', $this->bookingEmployee1));

        $this->assertEquals('14:00', substr((string) $this->bookingEmployee1->fresh()->booking_time, 0, 5));
    }

    public function test_payment_status_does_not_change_when_work_status_is_updated(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch1->id, 'cashier_id' => $this->owner->id,
            'employee_id' => $this->employee1->id, 'booking_id' => $this->bookingEmployee1->id,
            'transaction_code' => 'TRX-TEST-001', 'transaction_date' => now(),
            'subtotal' => 50000, 'total' => 50000, 'status' => StatusTransaksi::Pending,
        ]);

        $this->actingAs($this->employeeUser1)
            ->patch(route('bookings.update-status', $this->bookingEmployee1), [
                'status' => BookingStatus::InProgress->value,
            ])
            ->assertSessionHas('success');
        $this->assertEquals(StatusTransaksi::Pending, $tx->fresh()->status);

        $this->actingAs($this->employeeUser1)
            ->patch(route('bookings.update-status', $this->bookingEmployee1), [
                'status' => BookingStatus::Completed->value,
            ])
            ->assertSessionHas('success');
        $this->assertEquals(StatusTransaksi::Pending, $tx->fresh()->status);
    }
}
