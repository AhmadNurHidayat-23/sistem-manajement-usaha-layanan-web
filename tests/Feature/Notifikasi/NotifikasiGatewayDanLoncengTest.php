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
use App\Notifications\OperationalNotification;
use App\Services\OperationalNotificationService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotifikasiGatewayDanLoncengTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $cashier;

    protected User $customer1;

    protected User $customer2;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->branch = Branch::create(['name' => 'Cabang Utama', 'address' => 'Jl. Utama 1', 'is_active' => true, 'service_mode' => 'both']);

        $cat = ServiceCategory::create(['name' => 'Haircut']);
        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $cat->id,
            'name' => 'Potong Rambut',
            'price' => 60000,
            'is_active' => true,
            'service_type' => 'both',
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $custRole = Role::where('name', UserRole::Customer->value)->first();

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id, 'name' => 'Kasir Utama']);
        $this->cashier->branches()->attach($this->branch->id);

        $this->customer1 = User::factory()->create(['role_id' => $custRole->id, 'name' => 'Pelanggan 1']);
        $this->customer2 = User::factory()->create(['role_id' => $custRole->id, 'name' => 'Pelanggan 2']);
    }

    public function test_midtrans_pending_tidak_dianggap_lunas(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-PEND-001',
            'subtotal' => 60000,
            'discount' => 0,
            'total' => 60000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        Payment::create([
            'transaction_id' => $tx->id,
            'payment_method' => 'qris',
            'amount' => 60000,
            'status' => StatusPembayaran::Pending,
            'reference_number' => 'QRIS-PENDING',
        ]);

        $paidNotifs = $this->cashier->notifications()->where('data', 'like', '%cashier_paid%')->get();
        $this->assertCount(0, $paidNotifs);
    }

    public function test_settlement_sah_idempoten_dan_mencegah_duplikasi_notifikasi(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-SETTLE-001',
            'subtotal' => 60000,
            'discount' => 0,
            'total' => 60000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);
        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $this->service->id,
            'qty' => 1,
            'unit_price' => 60000,
            'subtotal' => 60000,
        ]);

        $service = app(OperationalNotificationService::class);

        // Settlement pertama
        $tx->update(['status' => StatusTransaksi::Paid]);
        $service->notifyBranchCashiersPaymentSuccess($tx);

        // Simulasi webhook kedua untuk settlement yang sama
        $service->notifyBranchCashiersPaymentSuccess($tx);

        $paidNotifs = $this->cashier->notifications()->where('data', 'like', "%cashier_paid_{$tx->id}%")->get();
        $this->assertCount(1, $paidNotifs, 'Notifikasi pembayaran berhasil harus idempoten.');
    }

    public function test_pembayaran_gagal_atau_expired_tidak_menghasilkan_notifikasi_lunas(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-FAIL-001',
            'subtotal' => 60000,
            'discount' => 0,
            'total' => 60000,
            'status' => StatusTransaksi::Cancelled,
            'transaction_date' => now(),
        ]);

        Payment::create([
            'transaction_id' => $tx->id,
            'payment_method' => 'qris',
            'amount' => 60000,
            'status' => StatusPembayaran::Failed,
            'reference_number' => 'QRIS-FAILED',
        ]);

        $this->assertCount(0, $this->cashier->notifications()->where('data', 'like', '%cashier_paid%')->get());
    }

    public function test_pelanggan_menerima_notifikasi_perubahan_status_sesuai_bok_f_008(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer1->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        // Konfirmasi
        $booking->update(['status' => BookingStatus::Confirmed]);
        $latest = $this->customer1->notifications()->get()->last();
        $this->assertSame('Booking Dikonfirmasi', $latest->data['title']);

        // Selesai
        $booking->update(['status' => BookingStatus::Completed]);
        $latest = $this->customer1->notifications()->get()->last();
        $this->assertSame('Layanan Selesai', $latest->data['title']);
    }

    public function test_feed_lonceng_dan_mark_as_read_terisolasi_per_pengguna(): void
    {
        $this->customer1->notify(new OperationalNotification(
            title: 'Notif Khusus User 1',
            message: 'Pesan rahasia',
            category: 'test',
        ));

        // Customer 2 tidak boleh melihat notifikasi Customer 1
        $this->actingAs($this->customer2)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJson(['unread_count' => 0, 'notifications' => []]);

        // Customer 1 melihat 1 unread notifikasi
        $res = $this->actingAs($this->customer1)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJson(['unread_count' => 1]);

        $notifId = $res->json('notifications.0.id');

        // Customer 2 mencoba mark as read milik Customer 1 -> 404
        $this->actingAs($this->customer2)
            ->postJson(route('notifications.read', ['id' => $notifId]))
            ->assertNotFound();

        // Customer 1 mark as read miliknya -> sukses
        $this->actingAs($this->customer1)
            ->postJson(route('notifications.read', ['id' => $notifId]))
            ->assertOk()
            ->assertJson(['success' => true]);

        // Unread count sekarang 0
        $this->actingAs($this->customer1)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJson(['unread_count' => 0]);
    }

    public function test_database_rollback_tidak_meninggalkan_notifikasi_palsu(): void
    {
        try {
            DB::transaction(function () {
                $booking = Booking::create([
                    'user_id' => $this->customer1->id,
                    'branch_id' => $this->branch->id,
                    'service_id' => $this->service->id,
                    'booking_date' => now()->toDateString(),
                    'booking_time' => '10:00',
                    'status' => BookingStatus::Pending,
                ]);

                throw new \RuntimeException('Simulasi kegagalan bisnis');
            });
        } catch (\RuntimeException $e) {
            // Expected rollback
        }

        $this->assertCount(0, $this->customer1->notifications);
        $this->assertCount(0, $this->cashier->notifications);
    }
}
