<?php

namespace Tests\Feature\Laporan;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsivitasLaporanMobileTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Promotion $promo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Studio Cabang Utama',
            'address' => 'Jl. Kebon Jeruk No. 12, Jakarta Barat, DKI Jakarta 11530',
            'phone' => '081234567890',
            'service_mode' => 'appointment',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Outlet Sudirman',
            'address' => 'Gedung Sentra Sudirman Lt. Dasar, Jl. Jenderal Sudirman Kav. 25, Jakarta Selatan',
            'phone' => '081298765432',
            'service_mode' => 'appointment',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Bambang Owner',
            'email' => 'owner@simaul.com',
            'role_id' => $ownerRole->id,
        ]);

        $customer = User::factory()->create([
            'name' => 'Pelanggan Setia',
            'email' => 'customer@simaul.com',
            'role_id' => $customerRole->id,
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Perawatan Kendaraan',
            'service_mode' => 'appointment',
        ]);

        $service = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Motor Premium & Coating',
            'price' => 150000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->promo = Promotion::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Promo Diskon Perawatan Akhir Tahun 2026',
            'code' => 'TAHUNBARU26',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 20,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(20),
            'status' => PromotionStatus::Active,
        ]);

        // Buat Transaksi Lunas
        $transaction = Transaction::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $customer->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-REP-RESP-01',
            'subtotal' => 150000,
            'discount' => 30000,
            'total' => 120000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'qty' => 1,
            'price' => 150000,
            'subtotal' => 150000,
        ]);

        PromotionUsage::create([
            'promotion_id' => $this->promo->id,
            'user_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'used_at' => Carbon::now(),
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'cash',
            'amount' => 120000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::now(),
        ]);
    }

    public function test_laporan_konsolidasi_dan_promo_renders_both_desktop_table_and_mobile_cards(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', ['period' => 'this_month']));

        $response->assertOk();

        // 1. Laporan Konsolidasi Seluruh Cabang:
        // Header section responsif
        $response->assertSee('Laporan Konsolidasi Seluruh Cabang');
        $response->assertSee('Cabang Aktif');

        // Desktop Table Container (>= 768px)
        $response->assertSee('hidden md:block overflow-x-auto', false);
        $response->assertSee('Alamat Outlet');

        // Mobile Cards Container (< 768px)
        $response->assertSee('block md:hidden p-3.5 sm:p-4 space-y-3', false);
        $response->assertSee($this->branch1->display_name);
        $response->assertSee('Jl. Kebon Jeruk No. 12, Jakarta Barat, DKI Jakarta 11530');
        $response->assertSee('Total Omzet');
        $response->assertSee('Jumlah Transaksi');
        $response->assertSee('Potongan Diskon');
        $response->assertSee('Rata-Rata Transaksi');
        $response->assertSee('Kontribusi');

        // 2. Penggunaan Promo & Kupon Diskon:
        $response->assertSee('Penggunaan Promo & Kupon Diskon', false);
        $response->assertSee('Total Dipakai:');
        $response->assertSee('Total Diskon:');

        // Desktop Table
        $response->assertSee('TAHUNBARU26');
        $response->assertSee('Promo Diskon Perawatan Akhir Tahun 2026');

        // Mobile Cards
        $response->assertSee('x digunakan');
        $response->assertSee('Jenis & Nilai Promo', false);
        $response->assertSee('Total Diskon Diberikan');
        $response->assertSee('Diskon 20%');
    }

    public function test_filter_periode_dan_cabang_works_seamlessly_with_responsive_views(): void
    {
        // Filter Cabang 1
        $responseBranch = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_month',
            'branch_id' => $this->branch1->id,
        ]));

        $responseBranch->assertOk();
        $responseBranch->assertSee($this->branch1->display_name);
        $responseBranch->assertSee('Penggunaan Promo & Kupon Diskon', false);

        // Filter Cabang 2 (tidak ada transaksi promo)
        $responseBranch2 = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_month',
            'branch_id' => $this->branch2->id,
        ]));

        $responseBranch2->assertOk();
        $responseBranch2->assertSee('Belum ada penggunaan kode promo pada periode ini.');
    }
}
