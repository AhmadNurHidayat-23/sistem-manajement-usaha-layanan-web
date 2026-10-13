<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntreanDanKetersediaanBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Service $service;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

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

        $this->branch2 = Branch::create([
            'name' => 'Cabang Kedua',
            'address' => 'Jl. Sudirman No. 2',
            'phone' => '0822222222',
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Grooming', 'description' => 'Layanan grooming']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Premium',
            'price' => 75000,
            'estimated_duration' => 90,
            'service_type' => 'both',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch1->id);

        $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Staff',
            'phone' => '0833333333',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_daily_queue_shows_appointments_and_orders(): void
    {
        $today = now()->toDateString();

        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $today,
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Processing,
        ]);

        $response = $this->actingAs($this->manager)->get(route('queue.index', ['date' => $today]));
        $response->assertStatus(200);
        $response->assertSee('Cabang Utama');
        $response->assertSee('Cuci Premium');
    }

    public function test_booking_availability_controller_returns_slots(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('bookings.available-slots', [
            'branch_id' => $this->branch1->id,
            'date' => now()->addDays(2)->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['slots']);
    }

    public function test_booking_availability_controller_returns_branch_employees(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('bookings.branch-employees', [
            'branch_id' => $this->branch1->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['employees']);
    }

    public function test_booking_status_controller_updates_status(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->manager)->patch(route('bookings.update-status', $booking), [
            'status' => BookingStatus::Confirmed->value,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }
}
