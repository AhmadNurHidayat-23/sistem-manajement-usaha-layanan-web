<?php

namespace Tests\Feature\Pembayaran;

use App\Enums\CashierShiftStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\CashierShift;
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

class MidtransPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch;

    protected Transaction $transaction;

    protected string $serverKey = 'SB-Mid-server-test-key-12345';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);

        $this->branch = Branch::create([
            'name' => 'Cabang Midtrans Test',
            'address' => 'Jl. Sandbox 123',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'General']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Ganti Oli & Filter',
            'price' => 175000,
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-MID-001',
            'subtotal' => 175000,
            'discount' => 0,
            'total' => 175000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $service->id,
            'qty' => 1,
            'unit_price' => 175000,
            'subtotal' => 175000,
        ]);

        // Default set driver ke midtrans untuk test suite ini
        Config::set('services.payment_gateway.driver', 'midtrans');
        Config::set('services.payment_gateway.midtrans.server_key', $this->serverKey);
        Config::set('services.payment_gateway.midtrans.client_key', 'SB-Mid-client-test-key');
        Config::set('services.payment_gateway.midtrans.is_production', false);
    }

    public function test_can_initiate_midtrans_qris_payment_via_core_api_and_saves_qr_url(): void
    {
        $qrUrl = 'https://api.sandbox.midtrans.com/v2/qris/mid-tx-qris-12345/qr-code';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, QRIS transaction is created',
                'transaction_id' => 'mid-tx-qris-12345',
                'order_id' => 'MID-TRX-MID-001-TEST',
                'gross_amount' => '175000.00',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => $qrUrl,
                    ],
                ],
                'qr_string' => '00020126580014ID.LINKAJA.WWW...',
                'expiry_time' => '2026-10-07 12:00:00',
            ], 201),
        ]);

        $response = $this->actingAs($this->cashier)
            ->post(route('payment.gateway.initiate', $this->transaction), [
                'method' => 'qris',
            ]);

        // Verifikasi Http Request ke Midtrans Sandbox Core API /v2/charge
        Http::assertSent(function ($request) {
            $hasBasicAuth = $request->hasHeader('Authorization', 'Basic '.base64_encode($this->serverKey.':'));
            $data = $request->data();

            $isQrisType = ($data['payment_type'] ?? '') === 'qris';
            $hasValidDetails = isset($data['transaction_details']['order_id'])
                && str_starts_with($data['transaction_details']['order_id'], 'MID-TRX-MID-001-')
                && $data['transaction_details']['gross_amount'] === 175000;

            $urlMatches = $request->url() === 'https://api.sandbox.midtrans.com/v2/charge';

            return $hasBasicAuth && $isQrisType && $hasValidDetails && $urlMatches;
        });

        // Verifikasi record Payment dibuat dengan status Pending
        $payment = Payment::where('transaction_id', $this->transaction->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(StatusPembayaran::Pending, $payment->status);
        $this->assertEquals(175000, $payment->amount);
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertStringStartsWith('MID-TRX-MID-001-', $payment->gateway_reference);

        // Verifikasi redirect langsung ke waiting page SIMAUL, BUKAN ke Midtrans Snap
        $response->assertRedirect(route('payment.gateway.waiting', $payment));

        // Verifikasi payload tersimpan dengan qr_url dari actions Midtrans
        $payload = $payment->gateway_payload;
        $this->assertIsArray($payload);
        $this->assertEquals('midtrans', $payload['gateway']);
        $this->assertEquals($qrUrl, $payload['qr_url']);
        $this->assertNull($payload['redirect_url']);
    }

    public function test_can_initiate_midtrans_snap_payment_for_virtual_account(): void
    {
        $snapToken = 'snap-token-sandbox-va123';
        $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken;

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => $snapToken,
                'redirect_url' => $redirectUrl,
            ], 201),
        ]);

        $response = $this->actingAs($this->cashier)
            ->post(route('payment.gateway.initiate', $this->transaction), [
                'method' => 'virtual_account',
            ]);

        $response->assertRedirect($redirectUrl);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        });
    }

    public function test_missing_credentials_handled_gracefully_without_network_request(): void
    {
        Config::set('services.payment_gateway.midtrans.server_key', '');
        Http::fake();

        $response = $this->actingAs($this->cashier)
            ->from(route('payment.gateway.show', $this->transaction))
            ->post(route('payment.gateway.initiate', $this->transaction), [
                'method' => 'qris',
            ]);

        $response->assertRedirect(route('payment.gateway.show', $this->transaction));
        $response->assertSessionHas('error');

        Http::assertNothingSent();

        $this->assertDatabaseMissing('payments', [
            'transaction_id' => $this->transaction->id,
        ]);
    }

    public function test_midtrans_api_error_response_handled_gracefully(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'error_messages' => ['Access denied due to unauthorized server key'],
            ], 401),
        ]);

        $response = $this->actingAs($this->cashier)
            ->from(route('payment.gateway.show', $this->transaction))
            ->post(route('payment.gateway.initiate', $this->transaction), [
                'method' => 'qris',
            ]);

        $response->assertRedirect(route('payment.gateway.show', $this->transaction));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('payments', [
            'transaction_id' => $this->transaction->id,
        ]);
    }

    public function test_midtrans_webhook_settlement_marks_payment_and_transaction_as_paid(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST01';
        $statusCode = '200';
        $grossAmount = '175000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => null,
            'gateway_reference' => $orderId,
            'gateway_payload' => ['gateway' => 'midtrans'],
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MIDTRANS-TX-999',
            'fraud_status' => 'accept',
        ]);

        $response->assertStatus(200);
        $response->assertSee('OK');

        $payment->refresh();
        $this->assertEquals(StatusPembayaran::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('MIDTRANS-TX-999', $payment->reference_number);

        $this->transaction->refresh();
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->status);
    }

    public function test_midtrans_webhook_capture_with_accept_fraud_status_marks_paid(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST02';
        $statusCode = '200';
        $grossAmount = '175000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'e_wallet',
            'channel' => 'gopay',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => null,
            'gateway_reference' => $orderId,
            'gateway_payload' => ['gateway' => 'midtrans'],
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
            'transaction_id' => 'MIDTRANS-TX-CAP-01',
        ]);

        $response->assertStatus(200);

        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->fresh()->status);
    }

    public function test_midtrans_webhook_pending_retains_pending_status(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST03';
        $statusCode = '201';
        $grossAmount = '175000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'virtual_account',
            'channel' => 'bca_va',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => $orderId,
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(StatusPembayaran::Pending, $payment->fresh()->status);
        $this->assertEquals(StatusTransaksi::Pending, $this->transaction->fresh()->status);
    }

    public function test_midtrans_webhook_expire_and_cancel_updates_status(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST04';
        $statusCode = '202';
        $grossAmount = '175000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => $orderId,
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(StatusPembayaran::Expired, $payment->fresh()->status);
    }

    public function test_midtrans_webhook_with_invalid_signature_is_handled_safely(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST05';

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => $orderId,
        ]);

        $response = $this->post(route('payment.webhook'), [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '175000.00',
            'signature_key' => 'invalid-tampered-signature-value',
            'transaction_status' => 'settlement',
        ]);

        // Controller returns 200 with error log to acknowledge webhook without crashing
        $response->assertStatus(200);
        $response->assertSee('WEBHOOK_ERROR');

        // Status payment tidak boleh berubah
        $this->assertEquals(StatusPembayaran::Pending, $payment->fresh()->status);
    }

    public function test_midtrans_duplicate_webhook_is_idempotent(): void
    {
        $orderId = 'MID-TRX-MID-001-TEST06';
        $statusCode = '200';
        $grossAmount = '175000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => $orderId,
        ]);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'MIDTRANS-TX-IDEMPOTENT',
        ];

        // First call
        $firstResponse = $this->post(route('payment.webhook'), $payload);
        $firstResponse->assertStatus(200);
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $firstPaidAt = $payment->fresh()->paid_at;

        // Second call with identical payload
        $secondResponse = $this->post(route('payment.webhook'), $payload);
        $secondResponse->assertStatus(200);
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $this->assertEquals($firstPaidAt->toDateTimeString(), $payment->fresh()->paid_at->toDateTimeString());
    }

    public function test_can_poll_midtrans_status_via_api(): void
    {
        $orderId = 'MID-TRX-MID-001-POLL';

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => $orderId,
        ]);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/'.$orderId.'/status' => Http::response([
                'order_id' => $orderId,
                'status_code' => '200',
                'gross_amount' => '175000.00',
                'transaction_status' => 'settlement',
                'transaction_id' => 'MIDTRANS-POLL-123',
            ], 200),
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('payment.gateway.poll', $payment));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'paid',
            'is_paid' => true,
        ]);

        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->fresh()->status);
    }

    public function test_pos_checkout_with_midtrans_qris_redirects_to_waiting_page_and_displays_qr(): void
    {
        $this->cashier->branches()->attach($this->branch);

        CashierShift::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => CashierShiftStatus::Open,
        ]);

        $qrCodeUrl = 'https://api.sandbox.midtrans.com/v2/qris/5555-pos/qr-code';

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, QRIS transaction is created',
                'transaction_id' => 'MID-POS-5555',
                'order_id' => 'MID-TRX-POS-123',
                'gross_amount' => '50000.00',
                'payment_type' => 'qris',
                'transaction_time' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => $qrCodeUrl,
                    ],
                ],
                'expiry_time' => now()->addMinutes(15)->toDateTimeString(),
            ], 201),
        ]);

        $service = Service::where('branch_id', $this->branch->id)->firstOrFail();

        $response = $this->actingAs($this->cashier)
            ->post(route('pos.transactions.store'), [
                'branch_id' => $this->branch->id,
                'customer_name' => 'Pelanggan QRIS Test',
                'customer_phone' => '081234567890',
                'items' => [
                    ['service_id' => $service->id, 'qty' => 1],
                ],
                'payment_method' => 'qris',
            ]);

        $latestTrx = Transaction::latest('id')->firstOrFail();
        $payment = Payment::where('transaction_id', $latestTrx->id)->firstOrFail();

        // Verifikasi request ke Core API /v2/charge
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://api.sandbox.midtrans.com/v2/charge'
                && ($data['payment_type'] ?? '') === 'qris';
        });

        // JANGAN langsung redirect ke struk
        $this->assertFalse($response->isRedirect(route('pos.transactions.receipt', $latestTrx)));

        // Arahkan ke waiting page SIMAUL (TIDAK redirect ke Midtrans Snap)
        $response->assertRedirect(route('payment.gateway.waiting', $payment));

        // Status transaksi & payment harus Pending
        $this->assertEquals(StatusTransaksi::Pending, $latestTrx->status);
        $this->assertEquals(StatusPembayaran::Pending, $payment->status);
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertEquals($qrCodeUrl, $payment->gateway_payload['qr_url']);
        $this->assertNull($payment->gateway_payload['redirect_url']);

        // Waiting page menampilkan QR Code dari Midtrans dan Menunggu Pembayaran
        $waitingResponse = $this->actingAs($this->cashier)
            ->get(route('payment.gateway.waiting', $payment));

        $waitingResponse->assertStatus(200);
        $waitingResponse->assertSee($qrCodeUrl);
        $waitingResponse->assertSee('Menunggu Pembayaran');
        $waitingResponse->assertSee($latestTrx->transaction_code);
    }

    public function test_receipt_is_blocked_while_payment_is_pending_and_only_accessible_after_paid(): void
    {
        $this->cashier->branches()->attach($this->branch);

        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 175000,
            'status' => StatusPembayaran::Pending,
            'gateway_reference' => 'MID-TEST-BLOCK',
        ]);

        // Saat masih Pending: akses struk dialihkan ke waiting page
        $responsePending = $this->actingAs($this->cashier)
            ->get(route('pos.transactions.receipt', $this->transaction));

        $responsePending->assertRedirect(route('payment.gateway.waiting', $payment));

        // Setelah pembayaran Paid: struk dapat diakses
        $payment->status = StatusPembayaran::Paid;
        $payment->paid_at = now();
        $payment->save();

        $this->transaction->status = StatusTransaksi::Paid;
        $this->transaction->save();

        $responsePaid = $this->actingAs($this->cashier)
            ->get(route('pos.transactions.receipt', $this->transaction));

        $responsePaid->assertStatus(200);
        $responsePaid->assertSee($this->transaction->transaction_code);
    }

    public function test_pos_checkout_with_cash_completes_immediately_to_receipt(): void
    {
        $this->cashier->branches()->attach($this->branch);

        CashierShift::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => CashierShiftStatus::Open,
        ]);

        $service = Service::where('branch_id', $this->branch->id)->firstOrFail();

        $response = $this->actingAs($this->cashier)
            ->post(route('pos.transactions.store'), [
                'branch_id' => $this->branch->id,
                'customer_name' => 'Pelanggan Tunai Langsung',
                'items' => [
                    ['service_id' => $service->id, 'qty' => 1],
                ],
                'payment_method' => 'cash',
                'cash_received' => 200000,
            ]);

        $latestTrx = Transaction::latest('id')->firstOrFail();

        // Pembayaran tunai langsung redirect ke struk
        $response->assertRedirect(route('pos.transactions.receipt', $latestTrx));
        $this->assertEquals(StatusTransaksi::Paid, $latestTrx->status);

        $payment = Payment::where('transaction_id', $latestTrx->id)->firstOrFail();
        $this->assertEquals(StatusPembayaran::Completed, $payment->status);
    }
}
