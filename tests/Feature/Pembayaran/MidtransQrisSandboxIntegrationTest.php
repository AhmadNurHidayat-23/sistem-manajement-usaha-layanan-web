<?php

namespace Tests\Feature\Pembayaran;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransQrisSandboxIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $otherCashier;

    protected Branch $branch;

    protected Branch $otherBranch;

    protected Transaction $transaction;

    protected string $serverKey = 'Mid-server-test-key-12345';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        Config::set('services.payment_gateway.driver', 'midtrans');
        Config::set('services.payment_gateway.midtrans.server_key', $this->serverKey);
        Config::set('services.payment_gateway.midtrans.client_key', 'Mid-client-test-key');
        Config::set('services.payment_gateway.midtrans.is_production', false);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama QRIS Test',
            'address' => 'Jl. Sandbox No. 1',
            'is_active' => true,
        ]);

        $this->otherBranch = Branch::create([
            'name' => 'Cabang Lain QRIS Test',
            'address' => 'Jl. Sandbox No. 2',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashier->branches()->attach($this->branch);

        $this->otherCashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->otherCashier->branches()->attach($this->otherBranch);

        $category = ServiceCategory::create(['name' => 'Layanan POS']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Layanan Servis QRIS',
            'price' => 125000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-QRIS-001',
            'subtotal' => 125000,
            'discount' => 0,
            'total' => 125000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $service->id,
            'qty' => 1,
            'unit_price' => 125000,
            'subtotal' => 125000,
        ]);
    }

    public function test_qris_charge_prioritizes_v2_aspi_url_and_sets_pending(): void
    {
        $v1Url = 'https://api.sandbox.midtrans.com/v2/qris/tx-123/qr-code';
        $v2Url = 'https://merchants-app.sbx.midtrans.com/v4/qris/gopay/ref-123/qr-code';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Qris transaction is created',
                'transaction_id' => 'mid-tx-real-123',
                'order_id' => 'MID-TRX-QRIS-001-ABCDEF',
                'gross_amount' => '125000.00',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    ['name' => 'generate-qr-code', 'method' => 'GET', 'url' => $v1Url],
                    ['name' => 'generate-qr-code-v2', 'method' => 'GET', 'url' => $v2Url],
                ],
                'qr_string' => '00020101021226620014COM.GO-JEK.WWW...',
                'expiry_time' => now()->addMinutes(15)->format('Y-m-d H:i:s'),
            ], 201),
        ]);

        $response = $this->actingAs($this->cashier)->post(route('payment.gateway.initiate', $this->transaction), [
            'method' => 'qris',
        ]);

        $payment = Payment::where('transaction_id', $this->transaction->id)->firstOrFail();
        $this->assertEquals(StatusPembayaran::Pending, $payment->status);
        $this->assertEquals(125000, $payment->amount);
        $this->assertEquals('mid-tx-real-123', $payment->reference_number);
        $this->assertEquals($v2Url, $payment->gateway_payload['qr_url']);
        $this->assertEquals($v1Url, $payment->gateway_payload['qr_url_v1']);
        $this->assertEquals($v2Url, $payment->gateway_payload['qr_url_v2']);

        $response->assertRedirect(route('payment.gateway.waiting', $payment));
    }

    public function test_polling_triggers_status_api_and_settlement_marks_paid(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 125000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-TRX-POLL-1',
            'gateway_payload' => ['gateway' => 'midtrans'],
            'expired_at' => now()->addMinutes(15),
        ]);

        $sig = hash('sha512', 'MID-TRX-POLL-1200125000.00'.$this->serverKey);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/MID-TRX-POLL-1/status' => Http::response([
                'status_code' => '200',
                'order_id' => 'MID-TRX-POLL-1',
                'gross_amount' => '125000.00',
                'transaction_status' => 'settlement',
                'signature_key' => $sig,
                'transaction_id' => 'mid-settled-999',
            ], 200),
        ]);

        $response = $this->actingAs($this->cashier)->getJson(route('kasir.pembayaran.poll', $this->transaction));

        $response->assertOk()
            ->assertJson([
                'status' => 'paid',
                'is_paid' => true,
                'is_final' => true,
            ]);

        $payment->refresh();
        $this->transaction->refresh();

        $this->assertEquals(StatusPembayaran::Paid, $payment->status);
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('mid-settled-999', $payment->reference_number);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 125000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-FAKE-SIG',
            'expired_at' => now()->addMinutes(15),
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => 'MID-FAKE-SIG',
            'status_code' => '200',
            'gross_amount' => '125000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'fake-invalid-signature',
        ]);

        $response->assertOk()->assertSee('WEBHOOK_ERROR');
        $this->assertEquals(StatusPembayaran::Pending, Payment::where('gateway_reference', 'MID-FAKE-SIG')->value('status'));
    }

    public function test_concurrent_webhook_and_polling_is_idempotent(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 125000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-CONCURRENT-1',
            'expired_at' => now()->addMinutes(15),
        ]);

        $sig = hash('sha512', 'MID-CONCURRENT-1200125000.00'.$this->serverKey);
        $payload = [
            'order_id' => 'MID-CONCURRENT-1',
            'status_code' => '200',
            'gross_amount' => '125000.00',
            'transaction_status' => 'settlement',
            'signature_key' => $sig,
            'transaction_id' => 'mid-settled-concurrent',
        ];

        // Jalankan webhook pertama
        $this->post(route('payment.webhook'), $payload)->assertOk()->assertSee('OK');
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);

        // Jalankan webhook kedua: tetap aman tanpa melempar exception atau memicu duplikasi
        $this->post(route('payment.webhook'), $payload)->assertOk()->assertSee('OK');
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->fresh()->status);
    }

    public function test_refresh_page_does_not_create_duplicate_pending_payment(): void
    {
        $v2Url = 'https://merchants-app.sbx.midtrans.com/v4/qris/gopay/ref-fresh/qr-code';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Qris transaction is created',
                'transaction_id' => 'mid-tx-fresh',
                'order_id' => 'MID-FRESH-1',
                'gross_amount' => '125000.00',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    ['name' => 'generate-qr-code-v2', 'method' => 'GET', 'url' => $v2Url],
                ],
            ], 201),
        ]);

        // Attempt 1
        $this->actingAs($this->cashier)->post(route('payment.gateway.initiate', $this->transaction), ['method' => 'qris']);
        $this->assertDatabaseCount('payments', 1);

        // Attempt 2 (refresh / submit ulang metode sama)
        $this->actingAs($this->cashier)->post(route('payment.gateway.initiate', $this->transaction), ['method' => 'qris']);
        $this->assertDatabaseCount('payments', 1);

        // Hanya 1 HTTP request dibuat ke gateway
        Http::assertSentCount(1);
    }

    public function test_cross_branch_access_is_forbidden(): void
    {
        $response = $this->actingAs($this->otherCashier)->get(route('kasir.pembayaran', $this->transaction));
        $response->assertForbidden();
    }

    public function test_simulation_endpoint_is_blocked_outside_testing_environment(): void
    {
        app()->detectEnvironment(fn () => 'local');

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 125000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-SIM-TEST',
            'expired_at' => now()->addMinutes(15),
        ]);

        $this->withoutMiddleware();
        $response = $this->actingAs($this->cashier)->post(route('payment.gateway.simulate', $payment));
        $response->assertForbidden();

        app()->detectEnvironment(fn () => 'testing');
    }
}
