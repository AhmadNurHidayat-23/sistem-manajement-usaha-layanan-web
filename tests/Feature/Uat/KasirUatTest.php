<?php

namespace Tests\Feature\Uat;

use App\Enums\BookingStatus;
use App\Enums\CashierShiftStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirUatTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch1;

    protected Service $service1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);

        $this->cashier = User::where('email', 'cashier@example.com')->firstOrFail();
        $this->branch1 = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $this->service1 = Service::where('branch_id', $this->branch1->id)->firstOrFail();
    }

    public function test_uat_kasir_login_shift_transactions_and_payment(): void
    {
        $response = $this->post('/login', [
            'email' => 'cashier@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('cashier.dashboard'));

        $dashboardResponse = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $dashboardResponse->assertStatus(200);

        $openShift = $this->actingAs($this->cashier)->post(route('pos.shift.open'), [
            'branch_id' => $this->branch1->id,
            'opening_cash' => 200000,
        ]);
        $openShift->assertRedirect();

        $dashboardActiveResponse = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $dashboardActiveResponse->assertStatus(200);
        $dashboardActiveResponse->assertSee('Pembayaran yang Perlu Ditangani');
        $dashboardActiveResponse->assertSee('Bayar di POS');
        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch1->id,
            'status' => CashierShiftStatus::Open->value,
        ]);

        $posResponse = $this->actingAs($this->cashier)->get(route('pos.index'));
        $posResponse->assertStatus(200);
        $posResponse->assertSee('Terminal POS');

        $validatePromo = $this->actingAs($this->cashier)->postJson(route('promotions.validate'), [
            'code' => 'DISKON10',
            'amount' => 200000,
            'branch_id' => $this->branch1->id,
        ]);
        $validatePromo->assertStatus(200);
        $validatePromo->assertJson(['valid' => true]);

        $storeTrx = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
            'branch_id' => $this->branch1->id,
            'customer_name' => 'Pelanggan Walk-In UAT',
            'promo_code' => 'DISKON10',
            'discount' => 20000,
            'payment_method' => 'cash',
            'cash_received' => 200000,
            'items' => [
                [
                    'service_id' => $this->service1->id,
                    'qty' => 1,
                    'price' => $this->service1->price,
                ],
            ],
        ]);
        $storeTrx->assertRedirect();

        $latestTrx = Transaction::latest('id')->firstOrFail();
        $this->assertEquals(StatusTransaksi::Paid, $latestTrx->status);

        $receipt = $this->actingAs($this->cashier)->get(route('pos.transactions.receipt', $latestTrx));
        $receipt->assertStatus(200);
        $receipt->assertSee($latestTrx->transaction_code);

        $detail = $this->actingAs($this->cashier)->get(route('pos.transactions.show', $latestTrx));
        $detail->assertStatus(200);
        $detail->assertSee($latestTrx->transaction_code);

        $history = $this->actingAs($this->cashier)->get(route('pos.transactions.index'));
        $history->assertStatus(200);
        $history->assertSee('Riwayat Transaksi');

        $closeShift = $this->actingAs($this->cashier)->post(route('pos.shift.close'), [
            'closing_cash' => 400000,
        ]);
        $closeShift->assertRedirect();
    }

    public function test_uat_kasir_transaction_from_booking_order_and_member_registration(): void
    {
        $this->actingAs($this->cashier)->post(route('pos.shift.open'), [
            'branch_id' => $this->branch1->id,
            'opening_cash' => 100000,
        ]);

        $registerMember = $this->actingAs($this->cashier)->post(route('members.store'), [
            'name' => 'Member Walk-In Baru',
            'phone' => '081299887766',
            'email' => 'memberwalkin@example.com',
            'redirect_to' => route('pos.index'),
        ]);
        $registerMember->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('users', ['phone' => '081299887766']);
        $newUser = User::where('phone', '081299887766')->firstOrFail();
        $this->assertDatabaseHas('memberships', ['user_id' => $newUser->id]);

        $booking = Booking::where('branch_id', $this->branch1->id)->first();
        if ($booking) {
            $bookingTrx = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
                'branch_id' => $this->branch1->id,
                'booking_id' => $booking->id,
                'customer_name' => $booking->user->name,
                'payment_method' => 'cash',
                'cash_received' => 200000,
                'items' => [
                    [
                        'service_id' => $booking->service_id,
                        'qty' => 1,
                        'price' => $booking->service->price,
                    ],
                ],
            ]);
            $bookingTrx->assertRedirect();
            $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
        }

        $order = Order::where('branch_id', $this->branch1->id)->first();
        if ($order) {
            $orderTrx = $this->actingAs($this->cashier)->post(route('pos.transactions.store'), [
                'branch_id' => $this->branch1->id,
                'order_id' => $order->id,
                'customer_name' => $order->user->name,
                'payment_method' => 'cash',
                'cash_received' => 200000,
                'items' => [
                    [
                        'service_id' => $order->service_id,
                        'qty' => 1,
                        'price' => $order->service->price,
                    ],
                ],
            ]);
            $orderTrx->assertRedirect();
            $this->assertEquals(StatusPesanan::Completed, $order->fresh()->status);
        }
    }
}
