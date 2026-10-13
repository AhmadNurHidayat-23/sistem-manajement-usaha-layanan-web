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

class AksesBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $customer;

    protected User $otherCustomer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
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

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Staff',
            'phone' => '0833333333',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
        $this->otherCustomer = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_rbac_access_restrictions(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Pending,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Pending,
        ]);

        $this->actingAs($this->otherCustomer)->get(route('bookings.show', $booking))->assertStatus(403);
        $this->actingAs($this->otherCustomer)->get(route('orders.show', $order))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('queue.index'))->assertStatus(403);

        $this->actingAs($this->owner)->get(route('bookings.show', $booking))->assertStatus(200);
        $this->actingAs($this->owner)->get(route('orders.show', $order))->assertStatus(200);
        $this->actingAs($this->owner)->get(route('queue.index'))->assertStatus(200);
    }

    public function test_booking_policy_comprehensive_role_authorization(): void
    {
        $bookingBranch1 = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Pending,
        ]);

        $bookingBranch2 = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch2->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '12:00',
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($this->owner)->get(route('bookings.show', $bookingBranch1))->assertOk();
        $this->actingAs($this->owner)->get(route('bookings.show', $bookingBranch2))->assertOk();
        $this->actingAs($this->owner)->get(route('bookings.edit', $bookingBranch1))->assertOk();

        $this->actingAs($this->manager)->get(route('bookings.show', $bookingBranch1))->assertOk();
        $this->actingAs($this->manager)->get(route('bookings.edit', $bookingBranch1))->assertOk();
        $this->actingAs($this->manager)->get(route('bookings.show', $bookingBranch2))->assertForbidden();
        $this->actingAs($this->manager)->get(route('bookings.edit', $bookingBranch2))->assertForbidden();

        $this->actingAs($this->employeeUser)->get(route('bookings.show', $bookingBranch1))->assertOk();
        $this->actingAs($this->employeeUser)->get(route('bookings.edit', $bookingBranch1))->assertForbidden();
        $this->actingAs($this->employeeUser)->get(route('bookings.show', $bookingBranch2))->assertForbidden();
        $this->actingAs($this->employeeUser)->get(route('bookings.edit', $bookingBranch2))->assertForbidden();

        $this->actingAs($this->customer)->get(route('bookings.show', $bookingBranch1))->assertOk();
        $this->actingAs($this->customer)->get(route('bookings.edit', $bookingBranch1))->assertOk();
        $this->actingAs($this->otherCustomer)->get(route('bookings.show', $bookingBranch1))->assertForbidden();
        $this->actingAs($this->otherCustomer)->get(route('bookings.edit', $bookingBranch1))->assertForbidden();
        $this->actingAs($this->otherCustomer)->patch(route('bookings.cancel', $bookingBranch1))->assertForbidden();
    }
}
