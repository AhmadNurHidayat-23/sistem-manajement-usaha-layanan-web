<?php

namespace Tests\Feature\Notifikasi;

use App\Jobs\KirimNotifikasiWhatsAppJob;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\NotificationLog;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\NotifikasiService;
use App\Services\WhatsAppService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppGatewayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.whatsapp.driver' => 'log']);
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', 'customer')->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '081234567890',
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Uji WhatsApp',
            'address' => 'Jl. Merdeka No. 10',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Kategori Uji']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Layanan Uji Integrasi',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
    }

    public function test_baileys_check_connection_returns_connected(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/health' => Http::response([
                'status' => 'connected',
                'connected' => true,
                'hasQr' => false,
            ], 200),
        ]);

        $whatsApp = new WhatsAppService;
        $status = $whatsApp->checkConnection();

        $this->assertTrue($status['success']);
        $this->assertTrue($status['connected']);
        $this->assertEquals('connected', $status['status']);
        $this->assertFalse($status['has_qr']);
        $this->assertStringContainsString('terhubung', $status['message']);
    }

    public function test_baileys_check_connection_returns_disconnected_with_qr(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/health' => Http::response([
                'status' => 'disconnected',
                'connected' => false,
                'hasQr' => true,
            ], 200),
        ]);

        $whatsApp = new WhatsAppService;
        $status = $whatsApp->checkConnection();

        $this->assertTrue($status['success']);
        $this->assertFalse($status['connected']);
        $this->assertEquals('disconnected', $status['status']);
        $this->assertTrue($status['has_qr']);
        $this->assertEquals('http://127.0.0.1:3001/qr', $status['qr_url']);
    }

    public function test_baileys_check_connection_handles_offline_server(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/health' => fn () => throw new ConnectionException('Connection refused'),
        ]);

        $whatsApp = new WhatsAppService;
        $status = $whatsApp->checkConnection();

        $this->assertFalse($status['success']);
        $this->assertFalse($status['connected']);
        $this->assertEquals('offline', $status['status']);
        $this->assertStringContainsString('offline', $status['message']);
    }

    public function test_log_check_connection_always_connected(): void
    {
        config(['services.whatsapp.driver' => 'log']);

        $whatsApp = new WhatsAppService;
        $status = $whatsApp->checkConnection();

        $this->assertTrue($status['connected']);
        $this->assertEquals('log', $status['driver']);
        $this->assertEquals('connected', $status['status']);
    }

    public function test_whatsapp_status_command(): void
    {
        config(['services.whatsapp.driver' => 'log']);

        $this->artisan('whatsapp:status')
            ->assertSuccessful();
    }

    public function test_whatsapp_test_command_sends_message_successfully(): void
    {
        config(['services.whatsapp.driver' => 'log']);

        $this->artisan('whatsapp:test', [
            'phone' => '081234567890',
            'message' => 'Pesan pengujian',
        ])
            ->assertSuccessful();
    }

    public function test_whatsapp_job_processes_and_updates_notification_log(): void
    {
        config(['services.whatsapp.driver' => 'log']);

        $job = new KirimNotifikasiWhatsAppJob(
            userId: $this->customer->id,
            phone: '081234567890',
            type: 'booking_created',
            message: 'Halo Booking Anda telah diterima',
        );

        $job->handle(app(WhatsAppService::class));

        $this->assertDatabaseHas('notification_logs', [
            'recipient' => '081234567890',
            'type' => 'booking_created',
            'status' => 'sent',
        ]);
    }

    public function test_notifikasi_service_deduplicates_duplicate_booking_messages(): void
    {
        config(['services.whatsapp.driver' => 'log']);

        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => 'pending',
        ]);

        $service = app(NotifikasiService::class);

        // First call creates a log and sends
        $log1 = $service->notifikasiBookingBaru($booking);
        $this->assertNotNull($log1);
        $this->assertEquals('sent', $log1->status);

        $countBefore = NotificationLog::where('type', 'booking_created')->count();
        $this->assertEquals(1, $countBefore);

        // Immediate second call should be caught by deduplication
        $log2 = $service->notifikasiBookingBaru($booking);
        $this->assertNotNull($log2);
        $this->assertEquals($log1->id, $log2->id);

        $countAfter = NotificationLog::where('type', 'booking_created')->count();
        $this->assertEquals(1, $countAfter);
    }

    public function test_test_number_rerouting_intercepts_recipient_phone(): void
    {
        config([
            'services.whatsapp.driver' => 'baileys',
            'services.whatsapp.baileys_url' => 'http://127.0.0.1:3001',
            'services.whatsapp.test_number' => '089999999999',
        ]);

        Http::fake([
            'http://127.0.0.1:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'BAILEYS-TEST-REROUTE',
            ], 200),
        ]);

        $whatsApp = new WhatsAppService;
        $result = $whatsApp->send('081234567890', 'Pesan Uji Coba Override');

        $this->assertTrue($result['success']);

        // Assert message was rerouted to test number instead of 6281234567890
        Http::assertSent(function ($request) {
            return $request['phone'] === '6289999999999';
        });
    }

    public function test_phone_masking_protects_sensitive_digits(): void
    {
        $masked = WhatsAppService::maskPhone('6281234567890');
        $this->assertStringStartsWith('6281', $masked);
        $this->assertStringEndsWith('890', $masked);
        $this->assertStringContainsString('***', $masked);
        $this->assertStringNotContainsString('34567', $masked);
    }
}
