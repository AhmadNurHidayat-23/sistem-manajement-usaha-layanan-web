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

class CustomerStreamlinedUxTest extends TestCase
{
    use RefreshDatabase;

    protected User $andi;

    protected User $budi;

    protected Branch $barbershop;

    protected Branch $laundry;

    protected Branch $autoCare;

    protected Service $barberService;

    protected Service $laundryService;

    protected Service $autoBothService;

    protected Service $autoDropService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();

        $this->andi = User::factory()->create(['role_id' => $customerRole->id, 'name' => 'Andi Pratama', 'email' => 'andi@simaul.com']);
        $this->budi = User::factory()->create(['role_id' => $customerRole->id, 'name' => 'Budi Santoso', 'email' => 'budi@simaul.com']);
        User::factory()->create(['role_id' => $cashierRole->id, 'name' => 'Kasir Demo', 'email' => 'kasir@simaul.com']);

        $this->barbershop = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. M.H. Thamrin No. 10',
            'service_mode' => 'appointment',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        $this->laundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. RS Fatmawati No. 45',
            'service_mode' => 'drop_off',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 8,
            'is_active' => true,
        ]);

        $this->autoCare = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Panjang No. 88',
            'service_mode' => 'both',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
            'max_slot_capacity' => 6,
            'is_active' => true,
        ]);

        $cat = ServiceCategory::create(['name' => 'Umum']);

        $this->barberService = Service::create([
            'branch_id' => $this->barbershop->id,
            'service_category_id' => $cat->id,
            'name' => 'Gentleman Haircut',
            'price' => 75000,
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $this->laundryService = Service::create([
            'branch_id' => $this->laundry->id,
            'service_category_id' => $cat->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 35000,
            'service_type' => 'drop_off',
            'unit' => 'kg',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $this->autoBothService = Service::create([
            'branch_id' => $this->autoCare->id,
            'service_category_id' => $cat->id,
            'name' => 'Detailing Interior Mobil',
            'price' => 250000,
            'service_type' => 'both',
            'estimated_duration' => 90,
            'is_active' => true,
        ]);

        $this->autoDropService = Service::create([
            'branch_id' => $this->autoCare->id,
            'service_category_id' => $cat->id,
            'name' => 'Cuci Helm Express',
            'price' => 30000,
            'service_type' => 'drop_off',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
    }

    public function test_guest_views_landing_page_and_customer_login_redirects_to_dashboard(): void
    {
        $this->get(route('customer.home'))->assertStatus(200)->assertSee('SIMAUL');

        $login = $this->post(route('login'), ['email' => 'andi@simaul.com', 'password' => 'password']);
        $login->assertRedirect(route('customer.dashboard'));
    }

    public function test_navbar_customer_area_has_no_duplicate_dashboard_buttons(): void
    {
        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Beranda');
        $response->assertSee('Aktivitas Saya');
        $response->assertSee('Member & Poin');
        $response->assertSee('Ulasan');
        $response->assertDontSee('id="header-dashboard-btn"', false);
    }

    public function test_each_business_appears_once_and_matches_actual_database_schedule(): void
    {
        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);

        $response->assertSee('Barbershop & Studio');
        $response->assertSee('Laundry & Shoe Care');
        $response->assertSee('Auto Care & Detailing');
        $response->assertSee($this->barbershop->operating_days_label);
        $response->assertSee($this->barbershop->operating_hours_label);
        $response->assertSee($this->laundry->operating_days_label);
        $response->assertSee($this->autoCare->operating_days_label);
        $response->assertDontSee('Fitur Pelanggan (SRS)');
    }

    public function test_ui_consistency_no_arrows_no_emojis_and_clean_branch_filter_names(): void
    {
        $home = $this->get(route('customer.home'));
        $home->assertStatus(200);
        $home->assertSee('Semua Cabang');
        $home->assertSee('Barbershop & Studio');
        $home->assertSee('Laundry & Shoe Care');
        $home->assertSee('Auto Care & Detailing');
        $home->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $home->assertDontSee('&rarr;', false);

        $dash = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $dash->assertStatus(200);
        $dash->assertSee('Lihat Layanan');
        $dash->assertSee('Detail Member');
        $dash->assertSee('Lihat Promo');
        $dash->assertDontSee('👋');
        $dash->assertDontSee('Detail Member →');
        $dash->assertDontSee('Lihat Promo →');
        $dash->assertDontSee('→');
        $dash->assertDontSee('←');

        $orderForm = $this->actingAs($this->andi)->get(route('customer.pesanan.create'));
        $orderForm->assertStatus(200);
        $orderForm->assertSee('Kembali');
        $orderForm->assertSee('Lihat Estimasi & Konfirmasi', false);
        $orderForm->assertDontSee('← Kembali');
    }

    public function test_service_modes_route_correctly_for_appointment_dropoff_and_both(): void
    {
        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);

        // Barbershop (appointment)
        $response->assertSee(route('customer.booking.create', ['branch_id' => $this->barbershop->id, 'service_id' => $this->barberService->id]));

        // Laundry (drop_off)
        $response->assertSee(route('customer.pesanan.create', ['branch_id' => $this->laundry->id, 'service_id' => $this->laundryService->id]));

        // Auto Care 'both'
        $response->assertSee(route('customer.booking.create', ['branch_id' => $this->autoCare->id, 'service_id' => $this->autoBothService->id]));
        $response->assertSee(route('customer.pesanan.create', ['branch_id' => $this->autoCare->id, 'service_id' => $this->autoBothService->id]));

        // Auto Care 'drop_off'
        $response->assertSee(route('customer.pesanan.create', ['branch_id' => $this->autoCare->id, 'service_id' => $this->autoDropService->id]));
    }

    public function test_aktivitas_saya_merges_appointments_and_orders_isolating_user_data(): void
    {
        $andiBooking = Booking::create([
            'user_id' => $this->andi->id,
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $andiOrder = Order::create([
            'user_id' => $this->andi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Processing,
        ]);

        $budiOrder = Order::create([
            'user_id' => $this->budi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Completed,
        ]);

        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);

        $response->assertSee(route('customer.booking.show', $andiBooking));
        $response->assertSee(route('customer.pesanan.show', $andiOrder));
        $response->assertDontSee(route('customer.pesanan.show', $budiOrder));
    }

    public function test_status_and_payment_status_remain_distinct_and_nav_routes_accessible(): void
    {
        $cashier = User::whereHas('role', fn ($q) => $q->where('name', 'cashier'))->first();
        $order = Order::create([
            'user_id' => $this->andi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Processing,
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'branch_id' => $this->laundry->id,
            'cashier_id' => $cashier->id,
            'transaction_code' => 'TRX-DISTINCT-TEST',
            'subtotal' => 35000,
            'discount' => 0,
            'total' => 35000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Diproses');
        $response->assertSee('Lunas');

        $this->actingAs($this->andi)->get(route('customer.member'))->assertStatus(200);
        $this->actingAs($this->andi)->get(route('customer.promo'))->assertStatus(200);
        $this->actingAs($this->andi)->get(route('customer.ulasan.index'))->assertStatus(200);
    }
}
