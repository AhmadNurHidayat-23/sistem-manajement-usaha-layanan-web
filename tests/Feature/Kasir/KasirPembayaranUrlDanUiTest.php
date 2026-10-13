<?php

namespace Tests\Feature\Kasir;

use App\Enums\CashierShiftStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirPembayaranUrlDanUiTest extends TestCase
{
    use RefreshDatabase;

    private User $kasirBarbershop;

    private User $kasirLaundry;

    private Branch $branchBarbershop;

    private Branch $branchLaundry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $roleKasir = Role::where('name', UserRole::Cashier->value)->first();

        $this->branchBarbershop = Branch::create([
            'name' => 'Barbershop & Studio',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '08123456781',
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 10,
            'is_active' => true,
        ]);

        $this->branchLaundry = Branch::create([
            'name' => 'Laundry & Shoe Care',
            'address' => 'Jl. Melati No. 2',
            'phone' => '08123456782',
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 10,
            'is_active' => true,
        ]);

        $this->kasirBarbershop = User::factory()->create([
            'name' => 'Kasir Barbershop',
            'email' => 'kasir@simaul.com',
            'role_id' => $roleKasir->id,
        ]);
        $this->kasirBarbershop->branches()->attach($this->branchBarbershop->id);

        $this->kasirLaundry = User::factory()->create([
            'name' => 'Kasir Laundry',
            'email' => 'kasir.laundry@simaul.com',
            'role_id' => $roleKasir->id,
        ]);
        $this->kasirLaundry->branches()->attach($this->branchLaundry->id);
    }

    public function test_url_pembayaran_menggunakan_identitas_publik_dan_tidak_mengekspos_id_database(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branchBarbershop->id,
            'cashier_id' => $this->kasirBarbershop->id,
            'transaction_code' => 'TRX-20261012-SAFE01',
            'transaction_date' => now(),
            'total' => 150000,
            'payment_status' => 'pending',
            'status' => StatusTransaksi::Pending,
        ]);

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'qris',
            'amount' => 150000,
            'status' => StatusPembayaran::Pending,
            'gateway_payload' => [
                'qr_url' => 'https://api.sandbox.midtrans.com/v2/qris/123/qr-code',
            ],
        ]);

        $url = route('kasir.pembayaran.waiting', $transaction->transaction_code);
        $this->assertStringContainsString('/kasir/pembayaran/menunggu/TRX-20261012-SAFE01', $url);
        $this->assertStringNotContainsString('/menunggu/'.$payment->id, $url);

        $response = $this->actingAs($this->kasirBarbershop)->get($url);
        $response->assertOk();
        $response->assertSee('TRX-20261012-SAFE01');
        $response->assertSee('Status Pembayaran');
        $response->assertSee('Ringkasan Pembayaran');
        $response->assertSee('Scan Kode QRIS');
        $response->assertSee('Barbershop & Studio');
        $response->assertSee('Ganti Metode Pembayaran');
        $response->assertDontSee('&rarr;');
        $response->assertDontSee('Detail Transaksi #');
    }

    public function test_sidebar_terminal_pos_aktif_pada_seluruh_alur_pembayaran_kasir(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branchBarbershop->id,
            'cashier_id' => $this->kasirBarbershop->id,
            'transaction_code' => 'TRX-20261012-POS001',
            'transaction_date' => now(),
            'total' => 75000,
            'payment_status' => 'pending',
            'status' => StatusTransaksi::Pending,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'qris',
            'amount' => 75000,
            'status' => StatusPembayaran::Pending,
            'gateway_payload' => ['qr_url' => 'https://example.com/qr.png'],
        ]);

        // 1. Menunggu Pembayaran
        $respWaiting = $this->actingAs($this->kasirBarbershop)
            ->get(route('kasir.pembayaran.waiting', $transaction->transaction_code));
        $respWaiting->assertOk();
        $respWaiting->assertSee('Terminal POS');
        $this->assertStringContainsString('bg-[#062072] text-white', $respWaiting->getContent());

        // 2. Pilih / Ganti Metode Pembayaran
        $respMetode = $this->actingAs($this->kasirBarbershop)
            ->get(route('kasir.pembayaran.show', $transaction->transaction_code));
        $respMetode->assertOk();
        $this->assertStringContainsString('bg-[#062072] text-white', $respMetode->getContent());
        $respMetode->assertSee('Pilih Metode Pembayaran');
        $respMetode->assertDontSee('Detail ›');
    }

    public function test_otorisasi_cabang_kasir_mencegah_akses_ke_transaksi_usaha_lain(): void
    {
        $transactionLaundry = Transaction::create([
            'branch_id' => $this->branchLaundry->id,
            'cashier_id' => $this->kasirLaundry->id,
            'transaction_code' => 'TRX-LAUNDRY-999',
            'transaction_date' => now(),
            'total' => 50000,
            'payment_status' => 'pending',
            'status' => StatusTransaksi::Pending,
        ]);

        Payment::create([
            'transaction_id' => $transactionLaundry->id,
            'payment_method' => 'qris',
            'amount' => 50000,
            'status' => StatusPembayaran::Pending,
        ]);

        // Kasir Barbershop mencoba mengakses pembayaran transaksi Laundry
        $response = $this->actingAs($this->kasirBarbershop)
            ->get(route('kasir.pembayaran.waiting', $transactionLaundry->transaction_code));

        $response->assertForbidden();
    }

    public function test_halaman_pembayaran_virtual_account_menampilkan_data_lengkap_tanpa_inline_onclick(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branchBarbershop->id,
            'cashier_id' => $this->kasirBarbershop->id,
            'transaction_code' => 'TRX-20261012-VA001',
            'transaction_date' => now(),
            'total' => 200000,
            'payment_status' => 'pending',
            'status' => StatusTransaksi::Pending,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'virtual_account',
            'amount' => 200000,
            'status' => StatusPembayaran::Pending,
            'gateway_payload' => [
                'bank' => 'bca',
                'va_number' => '1234567890123456',
                'va_display' => 'BCA Virtual Account',
            ],
        ]);

        $response = $this->actingAs($this->kasirBarbershop)
            ->get(route('kasir.pembayaran.waiting', $transaction->transaction_code));

        $response->assertOk();
        $response->assertSee('1234567890123456');
        $response->assertSee('Salin Nomor VA');
        $response->assertSee('data-copy-target="1234567890123456"', false);
        $this->assertStringNotContainsString('id="btn-copy-va" onclick=', $response->getContent());
    }

    public function test_pembayaran_berhasil_hanya_menampilkan_struk_dan_tidak_menampilkan_qr_aktif(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branchBarbershop->id,
            'cashier_id' => $this->kasirBarbershop->id,
            'transaction_code' => 'TRX-20261012-PAID01',
            'transaction_date' => now(),
            'total' => 125000,
            'payment_status' => 'paid',
            'status' => StatusTransaksi::Paid,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'qris',
            'amount' => 125000,
            'status' => StatusPembayaran::Paid,
            'paid_at' => now(),
            'gateway_payload' => [
                'qr_url' => 'https://example.com/qr.png',
            ],
        ]);

        $response = $this->actingAs($this->kasirBarbershop)
            ->get(route('kasir.pembayaran.waiting', $transaction->transaction_code));

        $response->assertOk();
        $response->assertSee('Pembayaran Berhasil Diterima');
        $response->assertSee('Lihat Struk');
        $response->assertDontSee('Buka Struk Transaksi');
        $response->assertDontSee('qris-image');
        $response->assertDontSee('Scan Kode QRIS');
    }

    public function test_terminal_pos_tidak_lagi_menampilkan_tombol_navigasi_duplikat(): void
    {
        CashierShift::create([
            'branch_id' => $this->branchBarbershop->id,
            'user_id' => $this->kasirBarbershop->id,
            'starting_cash' => 200000,
            'status' => CashierShiftStatus::Open,
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($this->kasirBarbershop)->get(route('pos.index'));
        $response->assertOk();
        $response->assertDontSee('Kelola Shift Kasir &rarr;', false);
        $response->assertSee('Kelola Shift Kasir');
    }
}
