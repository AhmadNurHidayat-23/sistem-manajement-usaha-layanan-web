<?php

namespace Tests\Feature;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatTransaksiKasirLunasTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierBranch1;

    protected User $cashierBranch2;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio',
            'address' => 'Jl. Barbershop No. 1',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care',
            'address' => 'Jl. Laundry No. 2',
            'is_active' => true,
        ]);

        $this->cashierBranch1 = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashierBranch1->branches()->attach($this->branch1);

        $this->cashierBranch2 = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashierBranch2->branches()->attach($this->branch2);
    }

    public function test_riwayat_transaksi_only_displays_paid_transactions_and_hides_pending(): void
    {
        $paidTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-PAID-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $paidTrx->id,
            'payment_method' => 'cash',
            'amount' => 100000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        $pendingTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-PENDING-001',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $pendingTrx->id,
            'payment_method' => 'qris',
            'amount' => 75000,
            'status' => StatusPembayaran::Pending,
        ]);

        $response = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index'));

        $response->assertOk();
        $response->assertSee('TRX-PAID-001');
        $response->assertDontSee('TRX-PENDING-001');
        $this->assertDatabaseHas('transactions', ['transaction_code' => 'TRX-PENDING-001']);
    }

    public function test_riwayat_transaksi_shows_actual_payment_methods_not_default(): void
    {
        $cashTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-CASH-99',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $cashTrx->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        $qrisTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-QRIS-99',
            'subtotal' => 85000,
            'discount' => 0,
            'total' => 85000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $qrisTrx->id,
            'payment_method' => 'qris',
            'amount' => 85000,
            'status' => StatusPembayaran::Paid,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index'));

        $response->assertOk();
        $this->assertEquals('TUNAI', $cashTrx->payment_method_label);
        $this->assertEquals('QRIS', $qrisTrx->payment_method_label);
        $response->assertSee('TUNAI');
        $response->assertSee('QRIS');
    }

    public function test_payment_method_filter_works_against_paid_transactions(): void
    {
        $cashTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-FLT-CASH',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $cashTrx->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        $qrisTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-FLT-QRIS',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $qrisTrx->id,
            'payment_method' => 'qris',
            'amount' => 75000,
            'status' => StatusPembayaran::Paid,
            'paid_at' => now(),
        ]);

        $resQris = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index', ['payment_method' => 'qris']));
        $resQris->assertOk()->assertSee('TRX-FLT-QRIS')->assertDontSee('TRX-FLT-CASH');

        $resCash = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index', ['payment_method' => 'cash']));
        $resCash->assertOk()->assertSee('TRX-FLT-CASH')->assertDontSee('TRX-FLT-QRIS');
    }

    public function test_branch_isolation_prevents_viewing_other_branches(): void
    {
        $trxBranch2 = Transaction::create([
            'branch_id' => $this->branch2->id,
            'cashier_id' => $this->cashierBranch2->id,
            'transaction_code' => 'TRX-OTHER-BRANCH',
            'subtotal' => 60000,
            'discount' => 0,
            'total' => 60000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $trxBranch2->id,
            'payment_method' => 'cash',
            'amount' => 60000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index'))->assertDontSee('TRX-OTHER-BRANCH');
        $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.show', $trxBranch2))->assertForbidden();
        $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.receipt', $trxBranch2))->assertForbidden();
    }

    public function test_receipt_is_blocked_for_unpaid_transaction(): void
    {
        $pendingTrx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-NOT-PAID',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $pendingTrx->id,
            'payment_method' => 'qris',
            'amount' => 50000,
            'status' => StatusPembayaran::Pending,
        ]);

        $response = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.receipt', $pendingTrx));
        $response->assertRedirect(route('kasir.pembayaran', $pendingTrx));
    }

    public function test_empty_state_and_waiting_view_clean_rendering(): void
    {
        $emptyRes = $this->actingAs($this->cashierBranch1)->get(route('pos.transactions.index'));
        $emptyRes->assertOk()->assertSee('Belum ada transaksi yang selesai dibayar.');

        $trx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierBranch1->id,
            'transaction_code' => 'TRX-WAIT-01',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        $v1Url = 'https://api.sandbox.midtrans.com/v2/qris/sample-v1/qr-code';
        $v2Url = 'https://merchants-app.sbx.midtrans.com/v4/qris/gopay/sample-v2/qr-code';

        Payment::create([
            'transaction_id' => $trx->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 50000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-WAIT-01',
            'gateway_payload' => [
                'gateway' => 'midtrans',
                'qr_url' => $v2Url,
                'qr_url_v1' => $v1Url,
                'qr_url_v2' => $v2Url,
            ],
            'expired_at' => now()->addMinutes(15),
        ]);

        $resWaiting = $this->actingAs($this->cashierBranch1)->get(route('kasir.pembayaran', $trx));
        $resWaiting->assertOk()
            ->assertSee('src="'.$v1Url.'"', false)
            ->assertSee('data-copy-target="'.$v2Url.'"', false)
            ->assertSee('Salin URL Gambar QRIS')
            ->assertDontSee('Buka Simulator')
            ->assertDontSee('Simulator Sandbox:')
            ->assertDontSee('Simulasi Pembayaran Berhasil');
    }
}
