<?php

namespace Tests\Feature\Notifikasi;

use App\Enums\BookingStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiKasirTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;

    protected Branch $branch2;

    protected User $cashier1;

    protected User $cashier2;

    protected User $customer;

    protected Service $service1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->branch1 = Branch::create(['name' => 'Cabang Pusat', 'address' => 'Jl. Pusat 1', 'is_active' => true, 'service_mode' => 'both']);
        $this->branch2 = Branch::create(['name' => 'Cabang Timur', 'address' => 'Jl. Timur 2', 'is_active' => true, 'service_mode' => 'both']);

        $cat = ServiceCategory::create(['name' => 'Haircut']);
        $this->service1 = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $cat->id,
            'name' => 'Potong Rambut Pria',
            'price' => 80000,
            'is_active' => true,
            'service_type' => 'both',
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $custRole = Role::where('name', UserRole::Customer->value)->first();

        $this->cashier1 = User::factory()->create(['role_id' => $cashierRole->id, 'name' => 'Kasir Pusat']);
        $this->cashier1->branches()->attach($this->branch1->id);

        $this->cashier2 = User::factory()->create(['role_id' => $cashierRole->id, 'name' => 'Kasir Timur']);
        $this->cashier2->branches()->attach($this->branch2->id);

        $this->customer = User::factory()->create(['role_id' => $custRole->id, 'name' => 'Rudi Pelanggan']);
    }

    public function test_booking_dengan_tagihan_mengirim_notifikasi_menunggu_ke_kasir_cabang_relevan(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $notifs = $this->cashier1->notifications;
        $this->assertCount(1, $notifs);
        $this->assertSame('Pembayaran Menunggu', $notifs->first()->data['title']);
        $this->assertSame('Bayar di POS', $notifs->first()->data['action_label']);
        $this->assertStringContainsString('80.000', $notifs->first()->data['message']);

        // Kasir cabang 2 tidak boleh menerima notifikasi transaksi cabang 1
        $this->assertCount(0, $this->cashier2->notifications);
    }

    public function test_qris_online_settlement_menghasilkan_notifikasi_lunas_ke_kasir(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier1->id,
            'transaction_code' => 'TRX-QRIS-001',
            'subtotal' => 80000,
            'discount' => 0,
            'total' => 80000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);
        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $this->service1->id,
            'qty' => 1,
            'unit_price' => 80000,
            'subtotal' => 80000,
        ]);

        $payment = Payment::create([
            'transaction_id' => $tx->id,
            'payment_method' => 'qris',
            'amount' => 80000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'QRIS-REF-001',
        ]);

        // Simulasikan gateway settlement
        $payment->update(['status' => StatusPembayaran::Completed, 'paid_at' => now()]);
        $tx->update(['status' => StatusTransaksi::Paid]);

        $notif = $this->cashier1->notifications()->where('data', 'like', '%cashier_paid%')->first();
        $this->assertNotNull($notif);
        $this->assertSame('Pembayaran Berhasil', $notif->data['title']);
        $this->assertSame('Lihat / Cetak Struk', $notif->data['action_label']);
        $this->assertStringContainsString('QRIS', $notif->data['message']);

        // Kasir 1 dapat membuka struk dari URL aksi
        $this->actingAs($this->cashier1)
            ->get($notif->data['action_url'])
            ->assertOk()
            ->assertSee('TRX-QRIS-001');

        // Kasir 2 (cabang lain) ditolak saat membuka struk
        $this->actingAs($this->cashier2)
            ->get($notif->data['action_url'])
            ->assertForbidden();
    }

    public function test_metode_bayar_lain_menghasilkan_notifikasi_lunas(): void
    {
        foreach (['e_wallet', 'virtual_account', 'cash'] as $method) {
            $tx = Transaction::create([
                'branch_id' => $this->branch1->id,
                'cashier_id' => $this->cashier1->id,
                'transaction_code' => 'TRX-'.strtoupper($method).'-001',
                'subtotal' => 80000,
                'discount' => 0,
                'total' => 80000,
                'status' => StatusTransaksi::Pending,
                'transaction_date' => now(),
            ]);
            TransactionDetail::create([
                'transaction_id' => $tx->id,
                'service_id' => $this->service1->id,
                'qty' => 1,
                'unit_price' => 80000,
                'subtotal' => 80000,
            ]);

            Payment::create([
                'transaction_id' => $tx->id,
                'payment_method' => $method,
                'amount' => 80000,
                'status' => StatusPembayaran::Completed,
                'reference_number' => 'REF-'.$method,
                'paid_at' => now(),
            ]);

            $tx->update(['status' => StatusTransaksi::Paid]);

            $notif = $this->cashier1->notifications()->where('data', 'like', '%TRX-'.strtoupper($method).'%')->first();
            $this->assertNotNull($notif);
            $this->assertSame('Pembayaran Berhasil', $notif->data['title']);
        }
    }

    public function test_transaksi_lunas_dialihkan_ke_struk_saat_diakses_di_pos(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier1->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-LUNAS-001',
            'subtotal' => 80000,
            'discount' => 0,
            'total' => 80000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        // Mengakses pos dengan booking_id yang sudah lunas langsung redirect ke struk
        $this->actingAs($this->cashier1)
            ->get(route('pos.index', ['booking_id' => $booking->uuid]))
            ->assertRedirect(route('pos.transactions.receipt', $tx));
    }

    public function test_transaksi_lunas_tampil_pada_riwayat_transaksi_kasir(): void
    {
        Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier1->id,
            'transaction_code' => 'TRX-HISTORY-001',
            'subtotal' => 80000,
            'discount' => 0,
            'total' => 80000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $this->actingAs($this->cashier1)
            ->get(route('pos.transactions.index'))
            ->assertOk()
            ->assertSee('TRX-HISTORY-001');
    }
}
