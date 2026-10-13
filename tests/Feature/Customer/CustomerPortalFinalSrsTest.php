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

class CustomerPortalFinalSrsTest extends TestCase
{
    use RefreshDatabase;

    protected User $andi;

    protected User $budi;

    protected Branch $barbershop;

    protected Branch $laundry;

    protected Branch $autoCare;

    protected Service $barberService;

    protected Service $laundryService;

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

        $catBarber = ServiceCategory::create(['name' => 'Haircut']);
        $catLaundry = ServiceCategory::create(['name' => 'Laundry Kiloan']);

        $this->barberService = Service::create([
            'branch_id' => $this->barbershop->id,
            'service_category_id' => $catBarber->id,
            'name' => 'Gentleman Haircut & Styling',
            'price' => 75000,
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $this->laundryService = Service::create([
            'branch_id' => $this->laundry->id,
            'service_category_id' => $catLaundry->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 35000,
            'service_type' => 'drop_off',
            'unit' => 'kg',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);
    }

    public function test_customer_dashboard_renders_with_streamlined_layout(): void
    {
        $response = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Halo, Andi Pratama!');
        $response->assertSee('Pilih Layanan');
        $response->assertSee('Barbershop & Studio');
        $response->assertSee('Laundry & Shoe Care');
        $response->assertSee('Auto Care & Detailing');
        $response->assertSee('Aktivitas Saya');
        $response->assertDontSee('Fitur Pelanggan (SRS)');
        $response->assertDontSee('id="header-dashboard-btn"', false);
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect('/login');
    }

    public function test_branch_display_name_accessor_removes_simaul_prefix_and_branch_suffix(): void
    {
        $this->assertSame('Barbershop & Studio', $this->barbershop->display_name);
        $this->assertSame('Laundry & Shoe Care', $this->laundry->display_name);
        $this->assertSame('Auto Care & Detailing', $this->autoCare->display_name);
    }

    public function test_branch_operating_days_helpers_work_accurately(): void
    {
        $this->assertFalse($this->barbershop->isOpenOnDay('senin'));
        $this->assertTrue($this->barbershop->isOpenOnDay('selasa'));
        $this->assertSame('Selasa - Minggu (Senin Libur)', $this->barbershop->operating_days_label);
        $this->assertTrue($this->laundry->isOpenOnDay('senin'));
        $this->assertSame('Setiap Hari', $this->laundry->operating_days_label);
    }

    public function test_appointment_booking_validates_operating_days(): void
    {
        $nextMonday = date('Y-m-d', strtotime('next monday'));
        $nextTuesday = date('Y-m-d', strtotime('next tuesday'));

        $this->actingAs($this->andi)->post(route('customer.booking.store'), [
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => $nextMonday,
            'booking_time' => '10:00',
        ])->assertSessionHasErrors(['booking_date']);

        $this->actingAs($this->andi)->post(route('customer.booking.store'), [
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => $nextTuesday,
            'booking_time' => '10:00',
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->andi->id,
            'branch_id' => $this->barbershop->id,
            'booking_date' => $nextTuesday.' 00:00:00',
        ]);
    }

    public function test_customer_cannot_view_other_customers_appointment(): void
    {
        $nextTuesday = date('Y-m-d', strtotime('next tuesday'));
        $booking = Booking::create([
            'user_id' => $this->andi->id,
            'branch_id' => $this->barbershop->id,
            'service_id' => $this->barberService->id,
            'booking_date' => $nextTuesday,
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($this->andi)->get(route('customer.booking.show', $booking))->assertStatus(200);
        $this->actingAs($this->budi)->get(route('customer.booking.show', $booking))->assertStatus(403);
    }

    public function test_andi_processing_order_cannot_be_reviewed(): void
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
            'transaction_code' => 'TRX-ANDI-TEST',
            'subtotal' => 35000,
            'discount' => 0,
            'total' => 35000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $this->actingAs($this->andi)->get(route('customer.ulasan.create', ['order_id' => $order->id]))
            ->assertRedirect(route('customer.pesanan.show', $order))->assertSessionHas('error');

        $this->actingAs($this->andi)->post(route('customer.ulasan.store'), [
            'order_id' => $order->id,
            'rating' => 5,
            'komentar' => 'Cepat dan bersih',
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('ulasans', ['user_id' => $this->andi->id]);
    }

    public function test_budi_completed_order_can_be_reviewed_and_cross_customer_forbidden(): void
    {
        $cashier = User::whereHas('role', fn ($q) => $q->where('name', 'cashier'))->first();
        $budiOrder = Order::create([
            'user_id' => $this->budi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Completed,
        ]);

        $budiTrx = Transaction::create([
            'order_id' => $budiOrder->id,
            'branch_id' => $this->laundry->id,
            'cashier_id' => $cashier->id,
            'transaction_code' => 'TRX-BUDI-TEST',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $this->actingAs($this->andi)->get(route('customer.ulasan.create', ['order_id' => $budiOrder->id]))->assertStatus(404);
        $this->actingAs($this->budi)->get(route('customer.ulasan.create', ['order_id' => $budiOrder->id]))->assertStatus(200);

        $this->actingAs($this->budi)->post(route('customer.ulasan.store'), [
            'order_id' => $budiOrder->id,
            'rating' => 5,
            'komentar' => 'Sangat puas dengan hasilnya!',
        ])->assertRedirect(route('customer.pesanan.show', $budiOrder));

        $this->assertDatabaseHas('ulasans', [
            'user_id' => $this->budi->id,
            'transaction_id' => $budiTrx->id,
            'rating' => 5,
            'komentar' => 'Sangat puas dengan hasilnya!',
        ]);
    }
}
