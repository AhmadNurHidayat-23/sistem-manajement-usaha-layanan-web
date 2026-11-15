<?php

namespace Tests\Feature;

use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\LayananSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembaruanHargaLayananRealistisTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierUser;

    protected Branch $branchBarber;

    protected Branch $branchLaundry;

    protected Branch $branchAuto;

    protected function setUp(): void
    {
        parent::setUp();

        $roleCashier = Role::firstOrCreate(['name' => 'cashier'], ['display_name' => 'Kasir']);

        $this->branchBarber = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Pusat No. 1',
            'phone' => '081234567891',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        $this->branchLaundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. Selatan No. 2',
            'phone' => '081234567892',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $this->branchAuto = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Barat No. 3',
            'phone' => '081234567893',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->cashierUser = User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir.semua@simaul.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCashier->id,
            'branch_id' => $this->branchLaundry->id,
        ]);
        $this->cashierUser->branches()->attach([$this->branchBarber->id, $this->branchLaundry->id, $this->branchAuto->id]);

        $this->seed(LayananSeeder::class);
    }

    public function test_seluruh_layanan_di_ketiga_cabang_memiliki_harga_dan_satuan_pasar_realistis(): void
    {
        // 1. Cabang Barbershop & Studio
        $potongPria = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Potong Rambut Pria')->firstOrFail();
        $this->assertEquals(50000, (float) $potongPria->price);
        $this->assertEquals('sesi', $potongPria->unit);

        $styling = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Potong Rambut & Styling')->firstOrFail();
        $this->assertEquals(65000, (float) $styling->price);
        $this->assertEquals('sesi', $styling->unit);

        $cukur = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Cukur & Rapikan Jenggot')->firstOrFail();
        $this->assertEquals(35000, (float) $cukur->price);
        $this->assertEquals('sesi', $cukur->unit);

        $scalp = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Perawatan Kulit Kepala')->firstOrFail();
        $this->assertEquals(75000, (float) $scalp->price);
        $this->assertEquals('treatment', $scalp->unit);

        $warna = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Pewarnaan Rambut')->firstOrFail();
        $this->assertEquals(140000, (float) $warna->price);
        $this->assertEquals('treatment', $warna->unit);

        $grooming = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Paket Grooming Lengkap')->firstOrFail();
        $this->assertEquals(95000, (float) $grooming->price);
        $this->assertEquals('sesi', $grooming->unit);

        // 2. Cabang Laundry & Shoe Care
        $laundryReguler = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Cuci Kering Setrika')->firstOrFail();
        $this->assertEquals(9000, (float) $laundryReguler->price);
        $this->assertEquals('kg', $laundryReguler->unit);

        $laundryExpress = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Laundry Express')->firstOrFail();
        $this->assertEquals(16000, (float) $laundryExpress->price);
        $this->assertEquals('kg', $laundryExpress->unit);

        $sepatu = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Deep Clean Sepatu')->firstOrFail();
        $this->assertEquals(45000, (float) $sepatu->price);
        $this->assertEquals('pasang', $sepatu->unit);

        $jas = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Dry Clean Jas & Kebaya')->firstOrFail();
        $this->assertEquals(60000, (float) $jas->price);
        $this->assertEquals('item', $jas->unit);

        $bedCover = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Cuci Bed Cover')->firstOrFail();
        $this->assertEquals(35000, (float) $bedCover->price);
        $this->assertEquals('item', $bedCover->unit);

        $karpet = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Cuci Karpet')->firstOrFail();
        $this->assertEquals(50000, (float) $karpet->price);
        $this->assertEquals('item', $karpet->unit);

        // 3. Cabang Auto Care & Detailing
        $cuciMobil = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Cuci Mobil Premium')->firstOrFail();
        $this->assertEquals(50000, (float) $cuciMobil->price);
        $this->assertEquals('kendaraan', $cuciMobil->unit);

        $interior = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Detailing Interior')->firstOrFail();
        $this->assertEquals(275000, (float) $interior->price);
        $this->assertEquals('unit', $interior->unit);

        $eksterior = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Perawatan Eksterior Mobil')->firstOrFail();
        $this->assertEquals(200000, (float) $eksterior->price);
        $this->assertEquals('unit', $eksterior->unit);

        $detailLengkap = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Detailing Mobil Lengkap')->firstOrFail();
        $this->assertEquals(550000, (float) $detailLengkap->price);
        $this->assertEquals('unit', $detailLengkap->unit);

        $coating = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Nano Ceramic Coating')->firstOrFail();
        $this->assertEquals(1250000, (float) $coating->price);
        $this->assertEquals('unit', $coating->unit);

        $helm = Service::where('branch_id', $this->branchAuto->id)->where('name', 'Cuci Helm')->firstOrFail();
        $this->assertEquals(30000, (float) $helm->price);
        $this->assertEquals('item', $helm->unit);
    }

    public function test_transaksi_baru_menggunakan_harga_layanan_terbaru(): void
    {
        CashierShift::create([
            'user_id' => $this->cashierUser->id,
            'branch_id' => $this->branchLaundry->id,
            'starting_cash' => 200000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $srvLaundry = Service::where('branch_id', $this->branchLaundry->id)->where('name', 'Cuci Kering Setrika')->firstOrFail();

        $payload = [
            'branch_id' => $this->branchLaundry->id,
            'customer_name' => 'Budi Santoso',
            'payment_method' => 'cash',
            'cash_received' => 50000,
            'items' => [
                ['service_id' => $srvLaundry->id, 'qty' => 5], // 5 kg x Rp 9.000 = Rp 45.000
            ],
        ];

        $response = $this->actingAs($this->cashierUser)->postJson(route('pos.transactions.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'transaction' => [
                'total' => 45000,
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'total' => 45000,
            'subtotal' => 45000,
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'service_id' => $srvLaundry->id,
            'price' => 9000,
            'qty' => 5,
            'subtotal' => 45000,
        ]);
    }

    public function test_transaksi_historis_tidak_berubah_nominalnya_setelah_harga_diperbarui(): void
    {
        $srvPotong = Service::where('branch_id', $this->branchBarber->id)->where('name', 'Potong Rambut Pria')->firstOrFail();

        // Buat rekaman transaksi lama dengan harga historis Rp 95.000
        $historicalTrx = Transaction::create([
            'transaction_code' => 'TRX-HISTORICAL-001',
            'branch_id' => $this->branchBarber->id,
            'cashier_id' => $this->cashierUser->id,
            'subtotal' => 95000,
            'discount' => 0,
            'total' => 95000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now()->subMonths(2),
        ]);

        TransactionDetail::create([
            'transaction_id' => $historicalTrx->id,
            'service_id' => $srvPotong->id,
            'qty' => 1,
            'price' => 95000,
            'subtotal' => 95000,
        ]);

        Payment::create([
            'transaction_id' => $historicalTrx->id,
            'amount' => 95000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'paid_at' => now()->subMonths(2),
        ]);

        // Verifikasi harga di master catalog adalah Rp 50.000
        $this->assertEquals(50000, (float) $srvPotong->fresh()->price);

        // Verifikasi transaksi historis tetap Rp 95.000 tidak terganggu
        $this->assertEquals(95000, (float) $historicalTrx->fresh()->total);
        $this->assertEquals(95000, (float) $historicalTrx->fresh()->subtotal);

        $historicalDetail = TransactionDetail::where('transaction_id', $historicalTrx->id)->firstOrFail();
        $this->assertEquals(95000, (float) $historicalDetail->price);
        $this->assertEquals(95000, (float) $historicalDetail->subtotal);
    }
}
