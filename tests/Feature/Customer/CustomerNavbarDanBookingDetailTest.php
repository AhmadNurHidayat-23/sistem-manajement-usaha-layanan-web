<?php

namespace Tests\Feature\Customer;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNavbarDanBookingDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Ahmad Pelanggan',
            'email' => 'ahmad@simaul.com',
        ]);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Studio Cabang Utama',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'phone' => '081234567890',
            'service_mode' => 'appointment',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 3,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $this->branch->id,
            'name' => 'Perawatan Rambut',
            'service_mode' => 'appointment',
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Potong Rambut Signature',
            'price' => 75000,
            'estimated_duration' => 45,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);
    }

    public function test_customer_navbar_shows_avatar_name_and_dropdown_with_profile_and_keluar(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.dashboard'));

        $response->assertOk();

        // 1. Desktop dropdown trigger
        $response->assertSee('id="customer-profile-menu"', false);
        $response->assertSee('id="customer-profile-btn"', false);
        $response->assertSee('Ahmad Pelanggan');
        $response->assertSee(strtoupper(substr($this->customer->name, 0, 2)));

        // 2. Dropdown content
        $response->assertSee('id="customer-profile-dropdown"', false);
        $response->assertSee('id="customer-profile-link"', false);
        $response->assertSee('Profil');
        $response->assertSee('Keluar');
        $response->assertSee(route('logout'));

        // 3. Mobile panel
        $response->assertSee('id="mobile-nav-panel"', false);
        $response->assertSee('id="mobile-profile-link"', false);
    }

    public function test_booking_create_page_shows_indonesian_labels_and_slot_time_legend(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.booking.create'));

        $response->assertOk();
        $response->assertSee('Buat Janji Temu');
        $response->assertSee('Kembali ke Janji Temu');
        $response->assertSee('Waktu Booking Tersedia');
        $response->assertSee('Status Jam:');
        $response->assertSee('Tersedia');
        $response->assertSee('Dipilih');
        $response->assertSee('Penuh');
        $response->assertSee('Lewat / Tutup');
        $response->assertSee('slots-container');
    }

    public function test_booking_detail_page_shows_indonesian_details_and_cancellation_confirmation_modal(): void
    {
        $nextDate = date('Y-m-d', strtotime('+2 days'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $nextDate,
            'booking_time' => '14:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('Detail Janji Temu');
        $response->assertDontSee('Janji Temu #');
        $response->assertSee('Kembali ke Janji Temu');
        $response->assertSee('Jadwal Pelaksanaan');
        $response->assertSee('Layanan Utama');
        $response->assertSee('Potong Rambut Signature');
        $response->assertSee('Rp 75.000');
        $response->assertSee('Menunggu Konfirmasi');

        // Modal trigger & dialog
        $response->assertSee('data-modal-open="modal-cancel-booking"', false);
        $response->assertSee('id="modal-cancel-booking"', false);
        $response->assertSee('Batalkan Janji Temu?');
        $response->assertSee('Ya, Batalkan');
        $response->assertSee('Kembali');
        $response->assertSee('Pilih Alasan Pembatalan');
        $response->assertSee('Jadwal mendadak');
        $response->assertSee('Salah memilih cabang');
        $response->assertSee('Salah memilih tanggal atau jam');
        $response->assertSee('Tidak dapat hadir');
        $response->assertSee('Tidak jadi menggunakan layanan');
        $response->assertSee('Alasan lainnya');
        $response->assertSee('id="other_cancellation_reason"', false);
        $response->assertSee(route('customer.booking.cancel', $booking));
    }

    public function test_booking_cancel_requires_reason(): void
    {
        $nextDate = date('Y-m-d', strtotime('+3 days'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $nextDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), []);

        $response->assertSessionHasErrors(['cancellation_reason']);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_booking_cancel_with_other_reason_requires_text(): void
    {
        $nextDate = date('Y-m-d', strtotime('+3 days'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $nextDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), [
                'cancellation_reason' => 'Alasan lainnya',
                'other_cancellation_reason' => '',
            ]);

        $response->assertSessionHasErrors(['other_cancellation_reason']);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_booking_cancel_action_cancels_booking_and_redirects_with_success_message(): void
    {
        $nextDate = date('Y-m-d', strtotime('+3 days'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $nextDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), [
                'cancellation_reason' => 'Salah memilih tanggal atau jam',
            ]);

        $response->assertRedirect(route('customer.booking.show', $booking));
        $response->assertSessionHas('success', 'Janji temu berhasil dibatalkan.');

        $freshBooking = $booking->fresh();
        $this->assertSame(BookingStatus::Cancelled, $freshBooking->status);
        $this->assertSame('Salah memilih tanggal atau jam', $freshBooking->cancellation_reason);

        // Periksa tampilan halaman detail menampilkan alasan pembatalan
        $showResponse = $this->actingAs($this->customer)->get(route('customer.booking.show', $booking));
        $showResponse->assertOk();
        $showResponse->assertSee('Janji Temu Dibatalkan');
        $showResponse->assertSee('Salah memilih tanggal atau jam');
    }

    public function test_booking_cancel_with_custom_other_reason_saves_and_displays_text(): void
    {
        $nextDate = date('Y-m-d', strtotime('+3 days'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $nextDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $customReason = 'Kendaraan sedang dalam perbaikan mendesak di bengkel lain';
        $response = $this->actingAs($this->customer)
            ->patch(route('customer.booking.cancel', $booking), [
                'cancellation_reason' => 'Alasan lainnya',
                'other_cancellation_reason' => $customReason,
            ]);

        $response->assertRedirect(route('customer.booking.show', $booking));
        $freshBooking = $booking->fresh();
        $this->assertSame(BookingStatus::Cancelled, $freshBooking->status);
        $this->assertSame($customReason, $freshBooking->cancellation_reason);

        $showResponse = $this->actingAs($this->customer)->get(route('customer.booking.show', $booking));
        $showResponse->assertOk();
        $showResponse->assertSee($customReason);
    }

    public function test_cancelled_booking_does_not_show_cancel_button_or_modal(): void
    {
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $pastDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Cancelled,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('Dibatalkan');
        $response->assertDontSee('data-modal-open="modal-cancel-booking"', false);
        $response->assertDontSee('id="modal-cancel-booking"', false);
    }
}
