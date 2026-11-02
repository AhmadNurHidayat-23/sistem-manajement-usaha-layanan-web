<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationReasonTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Uji Pembatalan',
            'address' => 'Jl. Uji No. 123',
            'phone' => '081234567890',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Kategori Uji',
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Perawatan Uji',
            'price' => 50000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);
    }

    public function test_customer_can_cancel_booking_with_preset_reason(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), [
                '_from_modal' => '1',
                'reason_preset' => 'Jadwal mendadak',
            ]);

        $response->assertRedirect(route('customer.booking.index'));
        $response->assertSessionHas('success');

        $fresh = $booking->fresh();
        $this->assertSame(BookingStatus::Cancelled, $fresh->status);
        $this->assertSame('Jadwal mendadak', $fresh->cancellation_reason);
    }

    public function test_customer_can_cancel_booking_with_custom_reason(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), [
                '_from_modal' => '1',
                'reason_preset' => 'Alasan lainnya',
                'reason_custom' => 'Kendaraan mogok sehingga tidak bisa menuju lokasi.',
            ]);

        $response->assertRedirect(route('customer.booking.index'));

        $fresh = $booking->fresh();
        $this->assertSame(BookingStatus::Cancelled, $fresh->status);
        $this->assertSame('Kendaraan mogok sehingga tidak bisa menuju lokasi.', $fresh->cancellation_reason);
    }

    public function test_cancel_validation_fails_if_custom_reason_is_empty_when_other_is_selected(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)
            ->from(route('customer.booking.show', $booking))
            ->patch(route('customer.booking.cancel', $booking), [
                '_from_modal' => '1',
                'reason_preset' => 'Alasan lainnya',
                'reason_custom' => '   ',
            ]);

        $response->assertRedirect(route('customer.booking.show', $booking));
        $response->assertSessionHasErrors(['reason_custom']);

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
        $this->assertNull($booking->fresh()->cancellation_reason);
    }

    public function test_detail_booking_displays_cancellation_reason(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '15:00',
            'status' => BookingStatus::Cancelled,
            'cancellation_reason' => 'Salah memilih cabang terdekat.',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('Janji Temu Dibatalkan');
        $response->assertSee('Salah memilih cabang terdekat.');
    }
}
