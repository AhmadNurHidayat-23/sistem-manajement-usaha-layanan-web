<?php

namespace Tests\Feature\Pembayaran;

use App\Enums\BookingStatus;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\NotifikasiService;
use App\Services\WhatsAppService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch;

    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone' => '081234567890',
        ]);

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

    public function test_notification_receipt_is_sent_and_logged(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->transaction->details->first()->service_id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Completed,
        ]);

        $this->transaction->update(['booking_id' => $booking->id]);

        $notifikasiService = app(NotifikasiService::class);
        $log = $notifikasiService->kirimStrukTransaksi($this->transaction);

        $this->assertNotNull($log);
        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->customer->id,
            'type' => 'transaction_receipt',
            'recipient' => WhatsAppService::normalizeIndonesianPhone($this->customer->phone),
        ]);
    }
}
