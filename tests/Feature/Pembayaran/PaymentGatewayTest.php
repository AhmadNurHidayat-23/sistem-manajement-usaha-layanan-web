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
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch;

    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.payment_gateway.driver' => 'sandbox']);
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test 123',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'General']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Servis Ringan',
            'price' => 150000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-001',
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $this->transaction->id,
            'service_id' => $service->id,
            'qty' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
        ]);
    }

    public function test_can_view_gateway_payment_page(): void
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('payment.gateway.show', $this->transaction));

        $response->assertStatus(200);
        $response->assertSee('TRX-TEST-001');
        $response->assertSee('QRIS');
        $response->assertSee('Virtual Account');
    }

    public function test_can_initiate_gateway_payment(): void
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('payment.gateway.initiate', $this->transaction), [
                'method' => 'qris',
            ]);

        $response->assertRedirect();

        $payment = Payment::where('transaction_id', $this->transaction->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(StatusPembayaran::Pending, $payment->status);
    }

    public function test_can_poll_payment_status(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 150000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-TEST-001',
            'gateway_reference' => 'GW-TEST-001',
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('payment.gateway.poll', $payment));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'pending',
            'is_paid' => false,
        ]);
    }

    public function test_can_simulate_payment_success_in_sandbox(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 150000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-TEST-002',
            'gateway_reference' => 'GW-TEST-002',
        ]);

        $response = $this->actingAs($this->cashier)
            ->post(route('payment.gateway.simulate', $payment));

        $response->assertRedirect(route('pos.transactions.receipt', $this->transaction));
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->fresh()->status);
    }

    public function test_payment_webhook_updates_status(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'va_bca',
            'amount' => 150000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-TEST-003',
            'gateway_reference' => 'GW-TEST-003',
        ]);

        $response = $this->post(route('payment.webhook'), [
            'gateway_reference' => 'GW-TEST-003',
            'transaction_status' => 'settlement',
            'paid_amount' => 150000,
            'paid_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $response->assertSee('OK');
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $this->transaction->fresh()->status);
    }

    public function test_waiting_page_does_not_contain_internal_simulation_panel(): void
    {
        $payment = Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'qris',
            'channel' => 'qris',
            'amount' => 150000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-TEST-QRIS-001',
            'gateway_reference' => 'MID-TRX-TEST-001',
            'gateway_payload' => [
                'qr_url' => 'https://api.sandbox.midtrans.com/v2/qris/test/qr-code',
            ],
            'expired_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('kasir.pembayaran.waiting', $this->transaction));

        $response->assertStatus(200);
        // Memastikan panel dan tombol simulasi internal tidak ada lagi
        $response->assertDontSee('Simulasi Pengujian Sandbox');
        $response->assertDontSee('Simulasi Pembayaran Berhasil');
        $response->assertDontSee('btn-simulate-success');
        // Memastikan kode QRIS dan instruksi pembayaran tetap ada
        $response->assertSee('Ringkasan Pembayaran');
        $response->assertSee('Scan Kode QRIS');
        $response->assertSee('Cara Melakukan Pembayaran');
    }
}
