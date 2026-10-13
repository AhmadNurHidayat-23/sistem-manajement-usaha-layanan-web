<?php

namespace Tests\Feature\Kasir;

use App\Enums\BookingStatus;
use App\Enums\CashierShiftStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\KasirPOS\Services\TransaksiService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Service $service1;

    protected Service $service2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Jendral Sudirman No. 10',
            'phone' => '08123456789',
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir Branch 1',
        ]);
        $this->cashier->branches()->attach($this->branch1->id);

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Customer Pos Test',
        ]);

        $category = ServiceCategory::create(['name' => 'Perawatan Sepatu']);

        $this->service1 = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Deep Clean Sepatu',
            'price' => 50000,
            'estimated_duration' => 60,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $this->service2 = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => null,
            'name' => 'Unyellowing Sol',
            'price' => 75000,
            'estimated_duration' => 90,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        CashierShift::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch1->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => CashierShiftStatus::Open,
        ]);
    }

    public function test_cashier_can_create_walkin_direct_transaction_with_cash(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'customer_name' => 'Budi Walk-in',
            'customer_phone' => '08123456789',
            'discount' => 5000,
            'items' => [
                ['service_id' => $this->service1->id, 'qty' => 2],
                ['service_id' => $this->service2->id, 'qty' => 1],
            ],
            'payment_method' => 'cash',
            'cash_received' => 200000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier->id,
            'subtotal' => 175000,
            'discount' => 5000,
            'total' => 170000,
            'status' => StatusTransaksi::Paid->value,
        ]);

        $transaction = Transaction::latest('id')->first();
        $response->assertRedirect(route('pos.transactions.receipt', $transaction));

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'service_id' => $this->service1->id,
            'qty' => 2,
            'price' => 50000,
            'subtotal' => 100000,
        ]);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'payment_method' => 'cash',
            'amount' => 170000,
            'status' => StatusPembayaran::Completed->value,
        ]);

        $payment = Payment::where('transaction_id', $transaction->id)->first();
        $this->assertStringContainsString('Kembali: Rp 30.000', $payment->reference_number);
    }

    public function test_cashier_can_create_transaction_linked_to_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'booking_id' => $booking->id,
            'customer_name' => $this->customer->name,
            'items' => [['service_id' => $this->service1->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'cash_received' => 50000,
        ]);

        $transaction = Transaction::latest('id')->first();
        $response->assertRedirect(route('pos.transactions.receipt', $transaction));
        $this->assertEquals($booking->id, $transaction->booking_id);
        $this->assertEquals(50000, $transaction->total);
        $this->assertEquals(StatusTransaksi::Paid, $transaction->status);
        // Status operasional booking tidak dipaksa menjadi Completed oleh pembayaran kasir
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_cashier_can_create_transaction_linked_to_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service2->id,
            'estimated_quantity' => 2,
            'pickup_option' => false,
            'status' => StatusPesanan::Processing,
        ]);

        $response = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'order_id' => $order->id,
            'customer_name' => $this->customer->name,
            'items' => [['service_id' => $this->service2->id, 'qty' => 2]],
            'payment_method' => 'transfer',
            'reference_number' => 'BCA-TRF-12345',
        ]);

        $transaction = Transaction::latest('id')->first();
        $response->assertRedirect(route('pos.transactions.receipt', $transaction));
        $this->assertEquals($order->id, $transaction->order_id);
        $this->assertEquals(150000, $transaction->total);
        $this->assertEquals(StatusTransaksi::Paid, $transaction->status);
        // Status operasional order drop-off tidak dipaksa menjadi Completed oleh pembayaran kasir
        $this->assertEquals(StatusPesanan::Processing, $order->fresh()->status);
    }

    public function test_cashier_can_pay_booking_that_is_already_completed(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '13:00',
            'status' => BookingStatus::Completed,
        ]);

        $response = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'booking_id' => $booking->id,
            'customer_name' => $this->customer->name,
            'items' => [['service_id' => $this->service1->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'cash_received' => 50000,
        ]);

        $transaction = Transaction::latest('id')->first();
        $response->assertRedirect(route('pos.transactions.receipt', $transaction));
        $this->assertEquals(StatusTransaksi::Paid, $transaction->status);
        // Booking yang sudah selesai operasional tetap berstatus Completed
        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
    }

    public function test_eligible_bookings_includes_completed_unpaid_and_excludes_paid(): void
    {
        $bookingUnpaid = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Completed,
        ]);

        $posService = app(TransaksiService::class);
        $eligibleBefore = $posService->getEligibleBookings($this->branch1->id);
        $this->assertTrue($eligibleBefore->contains('id', $bookingUnpaid->id));

        // Buat transaksi lunas
        $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'booking_id' => $bookingUnpaid->id,
            'customer_name' => $this->customer->name,
            'items' => [['service_id' => $this->service1->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'cash_received' => 50000,
        ]);

        $eligibleAfter = $posService->getEligibleBookings($this->branch1->id);
        $this->assertFalse($eligibleAfter->contains('id', $bookingUnpaid->id));
    }
}
