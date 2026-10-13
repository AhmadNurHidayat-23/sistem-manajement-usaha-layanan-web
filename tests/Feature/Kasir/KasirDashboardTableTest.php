<?php

namespace Tests\Feature\Kasir;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KasirDashboardTableTest extends TestCase
{
    use RefreshDatabase;

    protected User $kasirBarber;

    protected User $kasirLaundry;

    protected User $kasirAuto;

    protected User $customer;

    protected Branch $branchBarber;

    protected Branch $branchLaundry;

    protected Branch $branchAuto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('simaul:setup-demo-kasir');

        $this->kasirBarber = User::where('email', 'kasir@simaul.com')->first();
        $this->kasirLaundry = User::where('email', 'kasir.laundry@simaul.com')->first();
        $this->kasirAuto = User::where('email', 'kasir.autocare@simaul.com')->first();
        $this->customer = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))->first();

        $this->branchBarber = Branch::find(1);
        $this->branchLaundry = Branch::find(2);
        $this->branchAuto = Branch::find(3);
    }

    public function test_barbershop_hanya_menampilkan_appointment_tanpa_panel_atau_transaksi_dropoff(): void
    {
        $response = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));

        $response->assertOk();
        $response->assertSee('Pembayaran yang Perlu Ditangani');
        $response->assertSee('Mode: Appointment');
        $response->assertDontSee('Booking Siap Diproses');
        $response->assertDontSee('Pesanan Drop-off Siap Diproses');
        $response->assertDontSee('data-filter="drop_off"', false);

        // Pastikan tidak ada transaksi drop-off yang lolos
        $pendingPayments = $response->viewData('pendingPayments');
        $this->assertTrue($pendingPayments->every(fn ($p) => $p['type'] === 'appointment'));
    }

    public function test_laundry_hanya_menampilkan_dropoff_tanpa_panel_atau_transaksi_appointment(): void
    {
        $response = $this->actingAs($this->kasirLaundry)->get(route('cashier.dashboard'));

        $response->assertOk();
        $response->assertSee('Pembayaran yang Perlu Ditangani');
        $response->assertSee('Mode: Drop-off');
        $response->assertDontSee('Booking Siap Diproses');
        $response->assertDontSee('Pesanan Drop-off Siap Diproses');
        $response->assertDontSee('data-filter="appointment"', false);

        // Pastikan seluruh transaksi bertipe drop-off
        $pendingPayments = $response->viewData('pendingPayments');
        $this->assertTrue($pendingPayments->every(fn ($p) => $p['type'] === 'drop_off'));
    }

    public function test_auto_care_menampilkan_satu_tabel_dengan_filter_semua_appointment_dan_dropoff(): void
    {
        // 1. Filter default: Semua
        $respAll = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard'));
        $respAll->assertOk();
        $respAll->assertSee('Pembayaran yang Perlu Ditangani');
        $respAll->assertSee('Semua');
        $respAll->assertSee('Appointment');
        $respAll->assertSee('Drop-off');

        $paymentsAll = $respAll->viewData('pendingPayments');
        $typesInAll = $paymentsAll->pluck('type')->unique();

        // 2. Filter: Appointment saja
        $respAppt = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard', ['type' => 'appointment']));
        $respAppt->assertOk();
        $paymentsAppt = $respAppt->viewData('pendingPayments');
        $this->assertTrue($paymentsAppt->every(fn ($p) => $p['type'] === 'appointment'));

        // 3. Filter: Drop-off saja
        $respDrop = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard', ['type' => 'drop_off']));
        $respDrop->assertOk();
        $paymentsDrop = $respDrop->viewData('pendingPayments');
        $this->assertTrue($paymentsDrop->every(fn ($p) => $p['type'] === 'drop_off'));
    }

    public function test_hanya_transaksi_dengan_sisa_tagihan_positif_yang_masuk_ke_tabel(): void
    {
        $service = Service::where('branch_id', $this->branchBarber->id)->first();

        // Booking A: belum dibayar (sisa tagihan positif)
        $bookingUnpaid = Booking::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchBarber->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Booking B: sudah dibayar lunas
        $bookingPaid = Booking::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchBarber->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Completed,
        ]);

        Transaction::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchBarber->id,
            'cashier_id' => $this->kasirBarber->id,
            'booking_id' => $bookingPaid->id,
            'transaction_code' => 'TRX-TEST-PAID-01',
            'subtotal' => (float) $service->price,
            'discount' => 0,
            'total' => (float) $service->price,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));
        $pendingPayments = $response->viewData('pendingPayments');

        $this->assertTrue($pendingPayments->contains('id', $bookingUnpaid->id));
        $this->assertFalse($pendingPayments->contains('id', $bookingPaid->id));
    }

    public function test_booking_selesai_tetapi_belum_lunas_tetap_dapat_dibayar(): void
    {
        $service = Service::where('branch_id', $this->branchBarber->id)->first();

        $bookingDoneUnpaid = Booking::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchBarber->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Completed,
        ]);

        $response = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));
        $pendingPayments = $response->viewData('pendingPayments');

        $found = $pendingPayments->firstWhere('id', $bookingDoneUnpaid->id);
        $this->assertNotNull($found);
        $this->assertEquals((float) $service->price, $found['remaining_amount']);
        $this->assertStringContainsString('booking_id='.$bookingDoneUnpaid->id, $found['pay_url']);
    }

    public function test_isolasi_transaksi_antar_usaha_terjaga_dan_tidak_bocor(): void
    {
        // Kasir Barbershop mencoba memfilter type=drop_off tidak boleh menampilkan data cabang Laundry
        $respBarber = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard', ['type' => 'drop_off']));
        $respBarber->assertOk();

        $paymentsBarber = $respBarber->viewData('pendingPayments');
        // Barbershop tidak melayani drop-off, hasil harus tetap appointment miliknya atau kosong
        $this->assertTrue($paymentsBarber->every(fn ($p) => $p['type'] === 'appointment'));

        // Kasir Barbershop tidak boleh melihat order milik laundry
        $laundryOrders = Order::where('branch_id', $this->branchLaundry->id)->pluck('id');
        foreach ($laundryOrders as $orderId) {
            $this->assertFalse($paymentsBarber->contains(fn ($p) => $p['type'] === 'drop_off' && $p['id'] === $orderId));
        }
    }

    public function test_empat_kartu_statistik_dan_nilai_20px_tetap_dipertahankan(): void
    {
        $response = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));

        $response->assertOk();
        $response->assertSee('Status Shift');
        $response->assertSee('Transaksi Hari Ini');
        $response->assertSee('Penjualan Shift');
        $response->assertSee('Kas Tunai di Laci');
        $response->assertSee('text-[20px] font-semibold', false);
        $response->assertDontSee('Buka Terminal POS');
    }

    public function test_empty_state_tampil_sesuai_kondisi_filter(): void
    {
        // Bersihkan data branch Auto Care untuk pengujian empty state
        Booking::where('branch_id', $this->branchAuto->id)->delete();
        Order::where('branch_id', $this->branchAuto->id)->delete();

        $respAll = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard', ['type' => 'all']));
        $respAll->assertSee('Tidak ada pembayaran yang perlu ditangani.');

        $respAppt = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard', ['type' => 'appointment']));
        $respAppt->assertSee('Tidak ada pembayaran Appointment yang perlu ditangani.');

        $respDrop = $this->actingAs($this->kasirAuto)->get(route('cashier.dashboard', ['type' => 'drop_off']));
        $respDrop->assertSee('Tidak ada pembayaran Drop-off yang perlu ditangani.');
    }

    public function test_record_order_dropoff_pada_barbershop_seperti_ahmad_tidak_muncul_pada_dashboard(): void
    {
        $service = Service::where('branch_id', $this->branchBarber->id)->first() ?? Service::first();
        $orderDropoff = Order::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchBarber->id,
            'service_id' => $service->id,
            'status' => StatusPesanan::Pending,
        ]);

        $respBarber = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));
        $payments = $respBarber->viewData('pendingPayments');
        $this->assertFalse($payments->contains(fn ($p) => $p['type'] === 'drop_off' && $p['id'] === $orderDropoff->id));
    }

    public function test_setelah_transaksi_dibayar_lunas_langsung_hilang_dari_tabel_pembayaran(): void
    {
        $service = Service::where('branch_id', $this->branchBarber->id)->first();
        $booking = Booking::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchBarber->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '15:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $before = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));
        $this->assertTrue($before->viewData('pendingPayments')->contains('id', $booking->id));

        // Buat transaksi lunas
        Transaction::create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $this->branchBarber->id,
            'cashier_id' => $this->kasirBarber->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-TEST-LUNAS-'.Str::random(4),
            'subtotal' => (float) $service->price,
            'discount' => 0,
            'total' => (float) $service->price,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $after = $this->actingAs($this->kasirBarber)->get(route('cashier.dashboard'));
        $this->assertFalse($after->viewData('pendingPayments')->contains('id', $booking->id));
    }
}
