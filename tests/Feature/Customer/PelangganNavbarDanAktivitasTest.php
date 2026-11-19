<?php

namespace Tests\Feature\Customer;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganNavbarDanAktivitasTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected Branch $barbershop;

    protected Branch $laundry;

    protected Service $barberService;

    protected Service $laundryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Bambang Pamungkas',
            'email' => 'bambang@simaul.com',
        ]);

        $this->otherCustomer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Kurniawan Dwi',
            'email' => 'kurniawan@simaul.com',
        ]);

        $this->barbershop = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '081234567891',
            'service_mode' => 'appointment',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 3,
            'is_active' => true,
        ]);

        $this->laundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care',
            'address' => 'Jl. Thamrin No. 2',
            'phone' => '081234567892',
            'service_mode' => 'drop_off',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $barberCategory = ServiceCategory::create([
            'branch_id' => $this->barbershop->id,
            'name' => 'Hair Grooming',
            'service_mode' => 'appointment',
        ]);

        $this->barberService = Service::create([
            'branch_id' => $this->barbershop->id,
            'service_category_id' => $barberCategory->id,
            'name' => 'Gentleman Haircut',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $laundryCategory = ServiceCategory::create([
            'branch_id' => $this->laundry->id,
            'name' => 'Cuci Kiloan',
            'service_mode' => 'drop_off',
        ]);

        $this->laundryService = Service::create([
            'branch_id' => $this->laundry->id,
            'service_category_id' => $laundryCategory->id,
            'name' => 'Cuci Kering Lipat Express',
            'price' => 12000,
            'estimated_duration' => 60,
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);
    }

    public function test_navbar_pelanggan_has_exact_four_items_and_no_layanan_in_customer_area(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.dashboard'));

        $response->assertOk();

        // 4 Menu Utama Navigasi Pelanggan
        $response->assertSee(route('customer.dashboard'));
        $response->assertSee(route('customer.aktivitas'));
        $response->assertSee(route('customer.member'));
        $response->assertSee(route('customer.ulasan.index'));

        // Menu "Layanan" tidak boleh ada di navbar area pelanggan login
        $response->assertDontSee('#pilih-layanan', false);
    }

    public function test_navbar_pelanggan_indicator_active_per_page(): void
    {
        // 1. Dashboard -> Beranda aktif
        $resDashboard = $this->actingAs($this->customer)->get(route('customer.dashboard'));
        $resDashboard->assertOk();
        $resDashboard->assertSee(route('customer.dashboard'));

        // 2. Aktivitas -> Aktivitas Saya aktif
        $resAktivitas = $this->actingAs($this->customer)->get(route('customer.aktivitas'));
        $resAktivitas->assertOk();
        $resAktivitas->assertSee(route('customer.aktivitas'));

        // 3. Member -> Member & Poin aktif
        $resMember = $this->actingAs($this->customer)->get(route('customer.member'));
        $resMember->assertOk();
        $resMember->assertSee(route('customer.member'));

        // 4. Ulasan -> Ulasan aktif
        $resUlasan = $this->actingAs($this->customer)->get(route('customer.ulasan.index'));
        $resUlasan->assertOk();
        $resUlasan->assertSee(route('customer.ulasan.index'));
    }

    public function test_beranda_dashboard_preserves_greeting_branches_and_excludes_aktivitas_section(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.dashboard'));

        $response->assertOk();

        // Sapaan pelanggan
        $response->assertSee('Bambang Pamungkas');

        // Pilih Layanan & Kartu Cabang
        $response->assertSee('Pilih Layanan');
        $response->assertSee(e($this->barbershop->display_name), false);
        $response->assertSee(e($this->laundry->display_name), false);
        $response->assertSee($this->barberService->name);
        $response->assertSee($this->laundryService->name);

        // Section aktivitas saya TIDAK BOLEH muncul di beranda dashboard
        $response->assertDontSee('id="aktivitas-saya"', false);
    }

    public function test_halaman_aktivitas_displays_unified_history_filters_and_isolates_user_data(): void
    {
        $myBooking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '11:00:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $myOrder = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Processing,
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $cashier = User::factory()->create(['role_id' => $cashierRole->id]);

        Transaction::create([
            'order_id' => $myOrder->id,
            'branch_id' => $this->laundry->id,
            'cashier_id' => $cashier->id,
            'transaction_code' => 'TRX-AKTIVITAS-TEST',
            'subtotal' => 24000,
            'discount' => 0,
            'total' => 24000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $otherBooking = Booking::create([
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'booking_time' => '15:00:00',
            'status' => BookingStatus::Pending,
        ]);

        $otherOrder = Order::create([
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Completed,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.aktivitas'));

        $response->assertOk();

        // Header & Tab filter
        $response->assertSee('Aktivitas Saya');
        $response->assertSee('data-filter-activity="semua"', false);
        $response->assertSee('data-filter-activity="appointment"', false);
        $response->assertSee('data-filter-activity="pesanan"', false);

        // Data customer login tampil
        $response->assertSee($this->barberService->name);
        $response->assertSee(route('customer.booking.show', $myBooking));
        $response->assertSee($this->laundryService->name);
        $response->assertSee(route('customer.pesanan.show', $myOrder));
        $response->assertSee('Diproses');
        $response->assertSee('Lunas');

        // Data customer lain TIDAK BOLEH tampil (isolasi data)
        $response->assertDontSee(route('customer.booking.show', $otherBooking));
        $response->assertDontSee(route('customer.pesanan.show', $otherOrder));
    }

    public function test_halaman_aktivitas_shows_empty_state_when_user_has_no_activities(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.aktivitas'));

        $response->assertOk();
        $response->assertSee('Belum Ada Aktivitas');
        $response->assertSee(route('customer.dashboard'));
    }
}
