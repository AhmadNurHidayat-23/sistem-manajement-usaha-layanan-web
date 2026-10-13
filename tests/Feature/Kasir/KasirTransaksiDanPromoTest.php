<?php

namespace Tests\Feature\Kasir;

use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirTransaksiDanPromoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('simaul:setup-demo-kasir');
    }

    public function test_kasir_dapat_memproses_transaksi_walk_in(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $service = Service::where('branch_id', 1)->first();

        $response = $this->actingAs($kasir)->post(route('pos.transactions.store'), [
            'branch_id' => 1,
            'customer_name' => 'Pelanggan Walk-In',
            'customer_phone' => '081200001111',
            'payment_method' => 'cash',
            'cash_received' => 100000,
            'items' => [
                ['service_id' => $service->id, 'qty' => 1],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('transactions', [
            'branch_id' => 1,
            'cashier_id' => $kasir->id,
            'status' => StatusTransaksi::Paid->value,
        ]);
    }

    public function test_kasir_dapat_memfinalisasi_pembayaran_booking_dan_order(): void
    {
        $kasirBarber = User::where('email', 'kasir@simaul.com')->first();
        $booking = Booking::where('branch_id', 1)->whereDoesntHave('transaction')->first();
        $this->assertNotNull($booking);

        $responseBooking = $this->actingAs($kasirBarber)->post(route('pos.transactions.store'), [
            'branch_id' => 1,
            'booking_id' => $booking->id,
            'payment_method' => 'cash',
            'cash_received' => (float) $booking->service->price,
            'items' => [
                ['service_id' => $booking->service_id, 'qty' => 1],
            ],
        ]);

        $responseBooking->assertSessionHasNoErrors();
        $this->assertDatabaseHas('transactions', [
            'booking_id' => $booking->id,
            'status' => StatusTransaksi::Paid->value,
        ]);

        // Uji transaksi yang sudah lunas tidak dapat ditagih ulang
        $responseDuplicate = $this->actingAs($kasirBarber)->post(route('pos.transactions.store'), [
            'branch_id' => 1,
            'booking_id' => $booking->id,
            'payment_method' => 'cash',
            'items' => [
                ['service_id' => $booking->service_id, 'qty' => 1],
            ],
        ]);
        $responseDuplicate->assertSessionHasErrors(['booking_id']);
    }

    public function test_promo_valid_berhasil_diterapkan_dan_promo_tidak_valid_ditolak(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $service = Service::where('branch_id', 1)->first();

        // 1. Promo Valid BARBER15K (potongan Rp 15.000)
        $responseValid = $this->actingAs($kasir)->post(route('pos.transactions.store'), [
            'branch_id' => 1,
            'promo_code' => 'BARBER15K',
            'payment_method' => 'cash',
            'cash_received' => 100000,
            'items' => [
                ['service_id' => $service->id, 'qty' => 1],
            ],
        ]);

        $responseValid->assertSessionHasNoErrors();
        $latestTrx = Transaction::latest('id')->first();
        $this->assertEquals(15000, (float) $latestTrx->discount);
        $this->assertEquals((float) $service->price - 15000, (float) $latestTrx->total);

        // 2. Promo Tidak Valid
        $responseInvalid = $this->actingAs($kasir)->post(route('pos.transactions.store'), [
            'branch_id' => 1,
            'promo_code' => 'KODE_PALSU_TIDAK_ADA',
            'payment_method' => 'cash',
            'items' => [
                ['service_id' => $service->id, 'qty' => 1],
            ],
        ]);

        $responseInvalid->assertSessionHasErrors(['promo_code']);
    }

    public function test_kasir_dapat_melihat_dan_mengirim_struk_digital_transaksi(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $trx = Transaction::where('transaction_code', 'TRX-DEMO-BARBER-01')->first();

        $this->assertNotNull($trx);

        // Lihat struk
        $respReceipt = $this->actingAs($kasir)->get(route('pos.transactions.receipt', $trx));
        $respReceipt->assertOk();
        $respReceipt->assertSee($trx->transaction_code);
        $respReceipt->assertSee('Barbershop & Studio');

        // Kirim struk WA
        $respSend = $this->actingAs($kasir)->post(route('pos.transactions.send-receipt', $trx));
        $respSend->assertRedirect();
        $respSend->assertSessionHas('success');
    }
}
