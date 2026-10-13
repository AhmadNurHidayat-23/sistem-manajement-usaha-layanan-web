<?php

namespace Tests\Feature\Mobile;

use App\Enums\BookingStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\KasirPOS\Services\PaymentGatewayService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobilePaymentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_customer_can_initiate_midtrans_qris_payment_and_poll_status(): void
    {
        $qrUrl = 'https://api.sandbox.midtrans.com/v2/qris/mid-tx-test-999/qr-code';
        Config::set('services.payment_gateway.driver', 'midtrans');
        Config::set('services.payment_gateway.midtrans.server_key', 'SB-Mid-server-test-key-12345');
        Config::set('services.payment_gateway.midtrans.client_key', 'SB-Mid-client-test-key');
        Config::set('services.payment_gateway.midtrans.is_production', false);

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, QRIS transaction is created',
                'transaction_id' => 'mid-tx-test-999',
                'order_id' => 'MID-TRX-TEST-999',
                'gross_amount' => '80000.00',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => $qrUrl,
                    ],
                ],
            ], 201),
        ]);

        $branch = Branch::create(['name' => 'Cabang Bayar', 'address' => 'Jl. Bayar', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Salon']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Potong & Cuci',
            'price' => 80000,
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $userA = User::factory()->create(['role_id' => $customerRole?->id]);
        $userB = User::factory()->create(['role_id' => $customerRole?->id]);

        $booking = Booking::create([
            'user_id' => $userA->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '13:00',
            'status' => BookingStatus::Pending,
        ]);

        $loginA = $this->postJson('/api/mobile/login', ['email' => $userA->email, 'password' => 'password']);
        $tokenA = $loginA->json('data.token');

        // Customer A creates payment via QRIS
        $payRes = $this->withHeader('Authorization', 'Bearer '.$tokenA)->postJson('/api/mobile/customer/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'qris',
        ]);

        $payRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', 80000)
            ->assertJsonPath('data.payment_method', 'qris')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.qr_url', $qrUrl);

        $paymentId = $payRes->json('data.id');

        // Check payment detail
        $showRes = $this->withHeader('Authorization', 'Bearer '.$tokenA)->getJson("/api/mobile/customer/payments/{$paymentId}");
        $showRes->assertStatus(200)
            ->assertJsonPath('data.id', $paymentId)
            ->assertJsonPath('data.qr_url', $qrUrl);

        // Polling payment status
        $statusRes = $this->withHeader('Authorization', 'Bearer '.$tokenA)->getJson("/api/mobile/customer/payments/{$paymentId}/status");
        $statusRes->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.is_paid', false);

        // Simulate success payment in sandbox
        $simRes = $this->withHeader('Authorization', 'Bearer '.$tokenA)->postJson("/api/mobile/customer/payments/{$paymentId}/simulate");
        $simRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_paid', true);

        // Pembayaran di muka TIDAK mengubah status operasional pengerjaan menjadi Completed
        $this->assertEquals(BookingStatus::Pending, $booking->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $booking->fresh()->transaction->status);

        // Attempting to pay already paid booking returns 422
        $dupPayRes = $this->withHeader('Authorization', 'Bearer '.$tokenA)->postJson('/api/mobile/customer/payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'qris',
        ]);
        $dupPayRes->assertStatus(422)
            ->assertJsonPath('success', false);

        // Customer B cannot access Customer A's payment
        $loginB = $this->postJson('/api/mobile/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('data.token');

        $bPayAttempt = $this->withHeader('Authorization', 'Bearer '.$tokenB)->getJson("/api/mobile/customer/payments/{$paymentId}");
        $bPayAttempt->assertStatus(404);
    }

    public function test_pembayaran_di_muka_tidak_menyelesaikan_booking_dan_order(): void
    {
        $branch = Branch::create(['name' => 'Cabang Siklus', 'address' => 'Jl. Siklus', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Salon & Laundry']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Salon',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create(['role_id' => $customerRole?->id]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'estimated_quantity' => 1,
            'status' => StatusPesanan::Processing,
        ]);

        $txBooking = Transaction::create([
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-BOK-001',
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total' => 50000,
            'status' => StatusTransaksi::Pending,
        ]);

        $payBooking = Payment::create([
            'transaction_id' => $txBooking->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 50000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-BOK-001',
        ]);

        $txOrder = Transaction::create([
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'order_id' => $order->id,
            'transaction_code' => 'TRX-ORD-001',
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total' => 50000,
            'status' => StatusTransaksi::Pending,
        ]);

        $payOrder = Payment::create([
            'transaction_id' => $txOrder->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 50000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-ORD-001',
        ]);

        $gatewayService = app(PaymentGatewayService::class);
        $gatewayService->simulateSuccess($payBooking);
        $gatewayService->simulateSuccess($payOrder);

        // Status finansial menjadi Paid, status operasional pekerjaan tetap dipertahankan
        $this->assertEquals(StatusTransaksi::Paid, $txBooking->fresh()->status);
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);

        $this->assertEquals(StatusTransaksi::Paid, $txOrder->fresh()->status);
        $this->assertEquals(StatusPesanan::Processing, $order->fresh()->status);
    }

    public function test_penyelesaian_operasional_tetap_dapat_dilakukan_setelah_bayar_di_muka(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kerjakan', 'address' => 'Jl. Kerjakan', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Servis']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Servis Berkala',
            'price' => 75000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $empRole = Role::where('name', UserRole::Employee->value)->first();
        $userEmp = User::factory()->create(['role_id' => $empRole?->id, 'password' => Hash::make('password')]);
        $emp = Employee::create(['user_id' => $userEmp->id, 'branch_id' => $branch->id, 'name' => 'Teknisi', 'status' => 'active']);

        $custRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $custRole?->id]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'employee_id' => $emp->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $branch->id,
            'cashier_id' => $customer->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-DIMUKA-001',
            'transaction_date' => now(),
            'subtotal' => 75000,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
        ]);

        $payment = Payment::create([
            'transaction_id' => $tx->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 75000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-DIMUKA-001',
        ]);

        // Bayar di muka
        app(PaymentGatewayService::class)->simulateSuccess($payment);
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);

        // Karyawan login dan menyelesaikan tugas secara operasional
        $loginRes = $this->postJson('/api/mobile/login', ['email' => $userEmp->email, 'password' => 'password']);
        $token = $loginRes->json('data.token');

        $doneRes = $this->withHeader('Authorization', 'Bearer '.$token)->patchJson("/api/mobile/employee/tasks/{$booking->id}/status", [
            'status' => 'completed',
        ]);
        $doneRes->assertStatus(200);

        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $booking->fresh()->transaction->status);
    }
}
