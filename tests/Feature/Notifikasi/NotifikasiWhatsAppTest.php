<?php

namespace Tests\Feature\Notifikasi;

use App\Enums\BookingStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotifikasiService;
use App\Services\WhatsAppService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotifikasiWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $manager;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.whatsapp.driver' => 'log']);
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '081234567890',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'is_active' => true,
        ]);

        $this->manager->branches()->attach($this->branch);

        $category = ServiceCategory::create(['name' => 'Perawatan']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Servis Berkala',
            'price' => 100000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);
    }

    public function test_customer_registration_stores_phone_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '089876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'phone' => '089876543210',
        ]);
    }

    public function test_customer_registration_without_phone_stores_null_safely(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'phone' => null,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'siti@example.com',
            'phone' => null,
        ]);
    }

    public function test_booking_baru_creates_notification_log_for_customer(): void
    {
        $bookingDate = now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $bookingDate,
            'booking_time' => '10:00',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->customer->id,
            'channel' => 'whatsapp',
            'type' => 'booking_created',
            'recipient' => '081234567890',
            'status' => 'sent',
        ]);
    }

    public function test_booking_baru_without_phone_handled_safely_without_notification(): void
    {
        $customerNoPhone = User::factory()->create([
            'role_id' => Role::where('name', UserRole::Customer->value)->first()->id,
            'phone' => null,
        ]);

        $bookingDate = now()->addDays(2)->toDateString();

        $response = $this->actingAs($customerNoPhone)->post(route('bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => $bookingDate,
            'booking_time' => '11:00',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('notification_logs', [
            'user_id' => $customerNoPhone->id,
            'type' => 'booking_created',
        ]);
    }

    public function test_booking_confirmed_sends_specific_booking_confirmed_notification(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->manager)->patch(route('bookings.update-status', $booking), [
            'status' => BookingStatus::Confirmed->value,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->customer->id,
            'type' => 'booking_confirmed',
            'recipient' => '081234567890',
            'status' => 'sent',
        ]);
    }

    public function test_pesanan_baru_creates_notification_log_for_customer(): void
    {
        $orderService = Service::create([
            'service_category_id' => $this->service->service_category_id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Komplit',
            'price' => 50000,
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.store'), [
            'branch_id' => $this->branch->id,
            'service_id' => $orderService->id,
            'estimated_quantity' => 2,
            'pickup_option' => false,
            'notes' => 'Tolong hati-hati',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->customer->id,
            'channel' => 'whatsapp',
            'type' => 'order_created',
            'recipient' => '081234567890',
            'status' => 'sent',
        ]);
    }

    public function test_payment_webhook_paid_creates_notification_and_duplicate_is_idempotent(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Completed,
        ]);

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-NOTIF-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'digital_gateway',
            'channel' => 'qris',
            'amount' => 100000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'REF-WH-001',
            'gateway_reference' => 'GW-WH-001',
        ]);

        // First webhook call: pending -> paid
        $response1 = $this->post(route('payment.webhook'), [
            'gateway_reference' => 'GW-WH-001',
            'transaction_status' => 'settlement',
            'paid_amount' => 100000,
        ]);

        $response1->assertStatus(200);
        $this->assertEquals(StatusPembayaran::Paid, $payment->fresh()->status);

        $initialLogsCount = NotificationLog::where('type', 'payment_status')
            ->where('recipient', '081234567890')
            ->count();

        $this->assertEquals(1, $initialLogsCount);

        // Second webhook call (duplicate / retry): should NOT create another notification
        $response2 = $this->post(route('payment.webhook'), [
            'gateway_reference' => 'GW-WH-001',
            'transaction_status' => 'settlement',
            'paid_amount' => 100000,
        ]);

        $response2->assertStatus(200);

        $duplicateLogsCount = NotificationLog::where('type', 'payment_status')
            ->where('recipient', '081234567890')
            ->count();

        $this->assertEquals(1, $duplicateLogsCount);
    }

    public function test_driver_log_does_not_make_real_http_requests(): void
    {
        config(['services.whatsapp.driver' => 'log']);
        Http::fake();

        /** @var WhatsAppService $service */
        $service = app(WhatsAppService::class);
        $this->assertEquals('log', $service->getDriver());

        $result = $service->send('081234567890', 'Pesan Uji Coba');

        $this->assertTrue($result['success']);
        $this->assertEquals('logged', $result['raw']);
        Http::assertNothingSent();
    }

    public function test_fonnte_driver_sends_correct_payload_and_handles_response(): void
    {
        config([
            'services.whatsapp.driver' => 'fonnte',
            'services.whatsapp.fonnte_token' => 'dummy-fonnte-token-123',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281234567890'],
                'id' => ['FONNTE-MSG-001'],
                'process' => 'processing',
            ], 200),
        ]);

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Test Fonnte');

        $this->assertTrue($result['success']);
        $this->assertEquals('FONNTE-MSG-001', $result['reference']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'dummy-fonnte-token-123')
                && $request['target'] === '6281234567890'
                && $request['message'] === 'Pesan Test Fonnte'
                && $request['countryCode'] === '62';
        });
    }

    public function test_fonnte_driver_handles_missing_token_safely(): void
    {
        config([
            'services.whatsapp.driver' => 'fonnte',
            'services.whatsapp.fonnte_token' => '',
        ]);

        Http::fake();

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Test');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak dikonfigurasi', $result['raw']);
        Http::assertNothingSent();
    }

    public function test_fonnte_driver_handles_provider_error_safely(): void
    {
        config([
            'services.whatsapp.driver' => 'fonnte',
            'services.whatsapp.fonnte_token' => 'dummy-fonnte-token-123',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'device disconnected',
            ], 200),
        ]);

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Test');

        $this->assertFalse($result['success']);
        $this->assertEquals('device disconnected', $result['raw']);
    }

    public function test_baileys_driver_sends_correct_payload_and_handles_successful_response(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'BAILEYS-MSG-001',
                'phone' => '6281234567890',
            ], 200),
        ]);

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Uji Baileys');

        $this->assertTrue($result['success']);
        $this->assertEquals('BAILEYS-MSG-001', $result['reference']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3001/send-message'
                && $request['phone'] === '6281234567890'
                && $request['message'] === 'Pesan Uji Baileys';
        });
    }

    public function test_baileys_driver_handles_gateway_error_safely(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => Http::response([
                'success' => false,
                'error' => 'WhatsApp client belum terhubung. Silakan scan QR code terlebih dahulu.',
            ], 503),
        ]);

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Test');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum terhubung', $result['raw']);
    }

    public function test_baileys_driver_handles_connection_timeout_and_exception_gracefully(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => function () {
                throw new ConnectionException('Connection to Baileys timed out');
            },
        ]);

        $service = new WhatsAppService;
        $result = $service->send('081234567890', 'Pesan Test Timeout');

        $this->assertFalse($result['success']);
        $this->assertNull($result['reference']);
        $this->assertStringContainsString('Koneksi ke Baileys gateway gagal', $result['raw']);
    }

    public function test_baileys_driver_records_sent_status_in_notification_log(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'BAILEYS-BOOKING-999',
            ], 200),
        ]);

        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_code' => 'BOOK-BAI-001',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Confirmed,
            'total_amount' => 100000,
        ]);

        $notifikasiService = app(NotifikasiService::class);
        $log = $notifikasiService->notifikasiStatusBooking($booking);

        $this->assertNotNull($log);
        $this->assertEquals('sent', $log->status);
        $this->assertEquals('BAILEYS-BOOKING-999', $log->provider_reference);
        $this->assertNotNull($log->sent_at);
        $this->assertNull($log->error);
    }

    public function test_baileys_driver_records_failed_status_in_notification_log_when_down(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => function () {
                throw new ConnectionException('Gateway down');
            },
        ]);

        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_code' => 'BOOK-BAI-002',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '15:00',
            'status' => BookingStatus::Confirmed,
            'total_amount' => 100000,
        ]);

        $notifikasiService = app(NotifikasiService::class);
        $log = $notifikasiService->notifikasiStatusBooking($booking);

        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertStringContainsString('Koneksi ke Baileys gateway gagal', $log->error);
        $this->assertNull($log->sent_at);
    }
}
