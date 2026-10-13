<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiSlotBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected Branch $branch1;

    protected Employee $employee;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '0811111111',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Grooming',
            'description' => 'Layanan grooming',
        ]);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Premium',
            'description' => 'Cuci dan detailing',
            'price' => 75000,
            'estimated_duration' => 90,
            'service_type' => 'both',
            'is_active' => true,
        ]);

        $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Staff',
            'phone' => '0833333333',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
        $this->otherCustomer = User::factory()->create(['role_id' => $customerRole->id]);

        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => now()->addDays(2)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => now()->addDays(3)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);
    }

    public function test_customer_can_create_booking_with_slot_validation(): void
    {
        $targetDate = now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $targetDate,
            'booking_time' => '10:00',
            'notes' => 'Catatan pesanan',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($booking);
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertEquals($this->customer->id, $booking->user_id);
        $this->assertEquals($this->branch1->id, $booking->branch_id);
        $this->assertEquals($this->service->id, $booking->service_id);
        $this->assertEquals($this->employee->id, $booking->employee_id);
        $this->assertEquals($targetDate, $booking->booking_date->toDateString());
        $this->assertEquals('10:00', substr($booking->booking_time, 0, 5));
        $this->assertEquals(BookingStatus::Pending, $booking->status);
    }

    public function test_booking_validates_slot_capacity_and_prevents_clash(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);
        $targetDate = now()->addDays(2)->toDateString();

        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => $targetDate,
            'booking_time' => '09:00',
        ]);
        $this->assertEquals(1, Booking::whereDate('booking_date', $targetDate)->count());

        $response = $this->actingAs($this->otherCustomer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => $targetDate,
            'booking_time' => '09:00',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, Booking::whereDate('booking_date', $targetDate)->count());
    }

    public function test_booking_validates_employee_clash_when_employee_already_booked(): void
    {
        $this->branch1->update(['max_slot_capacity' => 5]);
        $targetDate = now()->addDays(2)->toDateString();

        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $targetDate,
            'booking_time' => '11:00',
        ]);
        $this->assertEquals(1, Booking::whereDate('booking_date', $targetDate)->count());

        $response = $this->actingAs($this->otherCustomer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $targetDate,
            'booking_time' => '11:00',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, Booking::whereDate('booking_date', $targetDate)->count());
    }

    public function test_customer_can_update_and_cancel_booking(): void
    {
        $targetDate = now()->addDays(3)->toDateString();
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => $targetDate,
            'booking_time' => '13:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)->put(route('bookings.update', $booking), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => $targetDate,
            'booking_time' => '14:00',
            'notes' => 'Ganti jam',
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $booking->refresh();
        $this->assertEquals('14:00', substr($booking->booking_time, 0, 5));
        $this->assertEquals('Ganti jam', $booking->notes);

        $cancelResponse = $this->actingAs($this->customer)->patch(route('bookings.cancel', $booking));
        $cancelResponse->assertRedirect(route('bookings.index'));
        $this->assertEquals(BookingStatus::Cancelled, $booking->fresh()->status);
    }
}
