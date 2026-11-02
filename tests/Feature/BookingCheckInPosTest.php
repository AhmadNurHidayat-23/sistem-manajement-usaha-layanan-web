<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCheckInPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $this->branch = Branch::create([
            'name' => 'Cabang Uji POS',
            'address' => 'Jl. Pengujian No. 1',
            'phone' => '081234567891',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Kategori POS',
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Cuci Sepatu Regular',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);
    }

    public function test_manager_can_access_booking_create_form(): void
    {
        $response = $this->actingAs($this->manager)->get(route('bookings.create'));

        $response->assertStatus(200);
    }

    public function test_cashier_can_check_in_pending_booking(): void
    {
        $booking = Booking::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00:00',
            'total_price' => 50000,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->cashier)->postJson(route('pos.bookings.check-in', $booking));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'confirmed',
        ]);

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
    }

    public function test_cashier_cannot_check_in_cancelled_booking(): void
    {
        $booking = Booking::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00:00',
            'total_price' => 50000,
            'status' => BookingStatus::Cancelled,
        ]);

        $response = $this->actingAs($this->cashier)->postJson(route('pos.bookings.check-in', $booking));

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $booking->refresh();
        $this->assertSame(BookingStatus::Cancelled, $booking->status);
    }

    public function test_pos_transaction_paid_does_not_set_booking_to_completed(): void
    {
        $booking = Booking::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00:00',
            'total_price' => 50000,
            'status' => BookingStatus::Confirmed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-TEST-001',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'transaction_date' => now(),
            'status' => StatusTransaksi::Paid,
        ]);

        $booking->refresh();
        // Payment in POS should NOT automatically set status to Completed
        // Completion is handled by operational execution / staff
        $this->assertNotSame(BookingStatus::Completed, $booking->status);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
    }
}
