<?php

namespace Tests\Feature\KasirPOS;

use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukUniversalTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Studio Pusat',
            'address' => 'Jl. Pemuda No. 45',
            'phone' => '081234567899',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);
    }

    public function test_struk_transaksi_menampilkan_disclaimer_universal_tanpa_istilah_laundry(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-TEST-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'payment_method' => 'cash',
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('pos.transactions.receipt', $transaction));

        $response->assertOk();
        $response->assertSee('Terima kasih telah menggunakan layanan SIMAUL.');
        $response->assertSee('Simpan struk ini sebagai bukti transaksi dan pembayaran yang sah.');

        $content = $response->getContent();
        $this->assertStringNotContainsString('Pusat Layanan Laundry Terpadu', $content);
        $this->assertStringNotContainsString('Pakaian yang tidak diambil', $content);
        $this->assertStringNotContainsString('font-mono', $content);
        $this->assertStringContainsString('font-sans', $content);
    }

    public function test_halaman_struk_memiliki_action_bar_rapi_dan_tidak_menampilkan_bayar_digital_saat_lunas(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-LUNAS-001',
            'subtotal' => 150000,
            'discount' => 10000,
            'total' => 140000,
            'payment_method' => 'cash',
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('pos.transactions.receipt', $transaction));

        $response->assertOk();
        $response->assertSee('Struk Transaksi');
        $response->assertSee('Terminal POS');
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('Cetak Struk');
        $response->assertSee('Kirim Struk WA');
        $response->assertSee('LUNAS');
        $response->assertDontSee('Bayar Digital');
    }

    public function test_halaman_struk_melakukan_canonical_redirect_ke_uuid_jika_diakses_lewat_kode_transaksi(): void
    {
        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-CANONICAL-001',
            'subtotal' => 80000,
            'discount' => 0,
            'total' => 80000,
            'payment_method' => 'cash',
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        $this->assertNotEmpty($transaction->uuid);

        // Akses via kode transaksi mentah harus redirect ke rute canonical UUID
        $rawUrl = '/pos/transactions/'.$transaction->transaction_code.'/receipt';
        $response = $this->actingAs($this->owner)->get($rawUrl);

        $response->assertRedirect(route('pos.transactions.receipt', $transaction));

        // Follow redirect harus berhasil 200 OK
        $followResponse = $this->actingAs($this->owner)->get(route('pos.transactions.receipt', $transaction));
        $followResponse->assertOk();
        $followResponse->assertSee($transaction->transaction_code);
    }
}
