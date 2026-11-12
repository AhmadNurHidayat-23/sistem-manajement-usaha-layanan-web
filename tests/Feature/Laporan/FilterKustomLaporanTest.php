<?php

namespace Tests\Feature\Laporan;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
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

class FilterKustomLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Service $service;

    protected Promotion $promo;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Studio Pusat',
            'address' => 'Jl. Merdeka No. 10, Jakarta Pusat',
            'phone' => '081234567890',
            'service_mode' => 'appointment',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Outlet Selatan',
            'address' => 'Jl. Sudirman No. 20, Jakarta Selatan',
            'phone' => '081234567891',
            'service_mode' => 'appointment',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner SIMAUL',
            'email' => 'owner.kustom@simaul.test',
            'role_id' => $ownerRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Uji',
            'email' => 'customer.kustom@simaul.test',
            'role_id' => $customerRole->id,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Mekanik',
            'email' => 'budi@simaul.test',
            'phone' => '081299990001',
            'status' => 'active',
        ]);

        $category = ServiceCategory::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Perawatan Kendaraan',
            'service_mode' => 'appointment',
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Motor Premium',
            'price' => 100000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->promo = Promotion::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Voucher Diskon Spesial',
            'code' => 'SPESIAL10',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 10000,
            'start_date' => Carbon::parse('2025-01-01'),
            'end_date' => Carbon::parse('2026-12-31'),
            'status' => PromotionStatus::Active,
        ]);

        $this->seedCustomTransactionsFixture();
    }

    protected function seedCustomTransactionsFixture(): void
    {
        // 1. Senin, 12 Januari 2026: (Monday = Senin, Date = 12, Month = 1, Year = 2026)
        // Subtotal 100.000, Diskon 10.000, Total 90.000 (Branch 1)
        $t1 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->owner->id,
            'employee_id' => $this->employee->id,
            'transaction_code' => 'TRX-KUS-01',
            'subtotal' => 100000,
            'discount' => 10000,
            'total' => 90000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::parse('2026-01-12 10:00:00'),
        ]);
        TransactionDetail::create([
            'transaction_id' => $t1->id,
            'service_id' => $this->service->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);
        Payment::create([
            'transaction_id' => $t1->id,
            'payment_method' => 'cash',
            'amount' => 90000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::parse('2026-01-12 10:15:00'),
        ]);
        PromotionUsage::create([
            'promotion_id' => $this->promo->id,
            'user_id' => $this->customer->id,
            'transaction_id' => $t1->id,
            'used_at' => Carbon::parse('2026-01-12 10:00:00'),
        ]);
        EmployeeCommission::create([
            'employee_id' => $this->employee->id,
            'transaction_id' => $t1->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 15000,
            'commission_amount' => 15000,
            'status' => CommissionStatus::Paid,
        ]);

        // 2. Rabu, 14 Januari 2026: (Wednesday = Rabu, Date = 14, Month = 1, Year = 2026)
        // Subtotal 200.000, Diskon 0, Total 200.000 (Branch 1)
        $t2 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-KUS-02',
            'subtotal' => 200000,
            'discount' => 0,
            'total' => 200000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::parse('2026-01-14 11:30:00'),
        ]);
        Payment::create([
            'transaction_id' => $t2->id,
            'payment_method' => 'qris',
            'amount' => 200000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::parse('2026-01-14 11:35:00'),
        ]);

        // 3. Senin, 09 Februari 2026: (Monday = Senin, Date = 9, Month = 2, Year = 2026)
        // Subtotal 150.000, Diskon 0, Total 150.000 (Branch 2)
        $t3 = Transaction::create([
            'branch_id' => $this->branch2->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-KUS-03',
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::parse('2026-02-09 14:00:00'),
        ]);
        Payment::create([
            'transaction_id' => $t3->id,
            'payment_method' => 'transfer',
            'amount' => 150000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::parse('2026-02-09 14:10:00'),
        ]);

        // 4. Kamis, 12 November 2026: (Thursday = Kamis, Date = 12, Month = 11, Year = 2026)
        // Subtotal 300.000, Diskon 30.000, Total 270.000 (Branch 1)
        $t4 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->owner->id,
            'employee_id' => $this->employee->id,
            'transaction_code' => 'TRX-KUS-04',
            'subtotal' => 300000,
            'discount' => 30000,
            'total' => 270000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::parse('2026-11-12 15:00:00'),
        ]);
        Payment::create([
            'transaction_id' => $t4->id,
            'payment_method' => 'card',
            'amount' => 270000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::parse('2026-11-12 15:10:00'),
        ]);
        PromotionUsage::create([
            'promotion_id' => $this->promo->id,
            'user_id' => $this->customer->id,
            'transaction_id' => $t4->id,
            'used_at' => Carbon::parse('2026-11-12 15:00:00'),
        ]);
        EmployeeCommission::create([
            'employee_id' => $this->employee->id,
            'transaction_id' => $t4->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 25000,
            'commission_amount' => 25000,
            'status' => CommissionStatus::Paid,
        ]);

        // 5. Rabu, 12 November 2025: (Wednesday = Rabu, Date = 12, Month = 11, Year = 2025)
        // Subtotal 250.000, Diskon 0, Total 250.000 (Branch 1)
        $t5 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-KUS-05',
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::parse('2025-11-12 16:00:00'),
        ]);
        Payment::create([
            'transaction_id' => $t5->id,
            'payment_method' => 'cash',
            'amount' => 250000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::parse('2025-11-12 16:05:00'),
        ]);
    }

    public function test_1_memilih_hari_saja_senin(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'day' => 'senin',
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Trx 1 (2026-01-12 Senin, 90.000) dan Trx 3 (2026-02-09 Senin, 150.000)
            return $report['summary']['total_transactions'] === 2
                && (float) $report['summary']['total_omzet'] === 240000.0
                && $report['formatted_period_label'] === 'Hari Senin (Semua Periode)';
        });
        $response->assertSee('Hari Senin');
    }

    public function test_2_memilih_tanggal_saja_12(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'date' => 12,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Trx 1 (12 Jan 2026, 90.000), Trx 4 (12 Nov 2026, 270.000), Trx 5 (12 Nov 2025, 250.000)
            return $report['summary']['total_transactions'] === 3
                && (float) $report['summary']['total_omzet'] === 610000.0
                && $report['formatted_period_label'] === 'Tanggal 12 (Semua Periode)';
        });
    }

    public function test_3_memilih_bulan_saja_januari(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'month' => 1,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Trx 1 (12 Jan 2026, 90.000) dan Trx 2 (14 Jan 2026, 200.000)
            return $report['summary']['total_transactions'] === 2
                && (float) $report['summary']['total_omzet'] === 290000.0
                && $report['formatted_period_label'] === 'Bulan Januari (Semua Tahun)';
        });
    }

    public function test_4_memilih_tahun_saja_2025(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'year' => 2025,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Hanya Trx 5 (12 Nov 2025, 250.000)
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 250000.0
                && $report['formatted_period_label'] === 'Tahun 2025';
        });
    }

    public function test_5_menggabungkan_beberapa_filter_bulan_dan_tahun(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'month' => 11,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Hanya Trx 4 (12 Nov 2026, 270.000), Trx 5 (2025) tidak termasuk
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 270000.0
                && (float) $report['summary']['total_discount'] === 30000.0
                && $report['formatted_period_label'] === 'November 2026';
        });
    }

    public function test_5_b_kombinasi_tanggal_dan_bulan(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'date' => 12,
            'month' => 11,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Trx 4 (12 Nov 2026, 270.000) dan Trx 5 (12 Nov 2025, 250.000)
            return $report['summary']['total_transactions'] === 2
                && (float) $report['summary']['total_omzet'] === 520000.0
                && $report['formatted_period_label'] === 'Tanggal 12 November (Semua Tahun)';
        });
    }

    public function test_6_menggunakan_keempat_filter_sekaligus(): void
    {
        // 12 Nov 2026 adalah hari Kamis
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'day' => 'kamis',
            'date' => 12,
            'month' => 11,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 270000.0
                && $report['formatted_period_label'] === 'Kamis, 12 November 2026';
        });
    }

    public function test_7_membiarkan_seluruh_filter_kosong_menampilkan_seluruh_data(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'day' => '',
            'date' => '',
            'month' => '',
            'year' => '',
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            // Total 5 transaksi: 90k + 200k + 150k + 270k + 250k = 960k
            return $report['summary']['total_transactions'] === 5
                && (float) $report['summary']['total_omzet'] === 960000.0
                && $report['formatted_period_label'] === 'Semua Data Transaksi';
        });
    }

    public function test_8_mengubah_filter_cabang_bersamaan_dengan_filter_kustom(): void
    {
        // Filter hari Senin pada Cabang 1
        $responseBranch1 = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'day' => 'senin',
            'branch_id' => $this->branch1->id,
        ]));

        $responseBranch1->assertOk();
        $responseBranch1->assertViewHas('report', function ($report) {
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 90000.0;
        });

        // Filter hari Senin pada Cabang 2
        $responseBranch2 = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'day' => 'senin',
            'branch_id' => $this->branch2->id,
        ]));

        $responseBranch2->assertOk();
        $responseBranch2->assertViewHas('report', function ($report) {
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 150000.0;
        });
    }

    public function test_9_konsistensi_data_antara_layar_pdf_dan_excel(): void
    {
        $params = [
            'period' => 'custom',
            'month' => 11,
            'year' => 2026,
            'branch_id' => $this->branch1->id,
        ];

        // Layar Web
        $responseWeb = $this->actingAs($this->owner)->get(route('reports.index', $params));
        $responseWeb->assertOk();
        $responseWeb->assertViewHas('report', function ($report) {
            return $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 270000.0
                && (float) $report['summary']['total_discount'] === 30000.0;
        });

        // Ekspor Excel
        $responseExcel = $this->actingAs($this->owner)->get(route('reports.export.excel', $params));
        $responseExcel->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $responseExcel->headers->get('Content-Disposition'));

        // Ekspor PDF
        $responsePdf = $this->actingAs($this->owner)->get(route('reports.export.pdf', $params));
        $responsePdf->assertOk();
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());
    }

    public function test_10_markup_dropdown_dan_keempat_opsi_select_tersedia(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', ['period' => 'custom']));

        $response->assertOk();

        // Elemen kontainer dropdown kustom
        $response->assertSee('id="custom-period-dropdown-wrapper"', false);
        $response->assertSee('id="btn-toggle-custom-period"', false);
        $response->assertSee('id="report-custom-filter-dropdown"', false);
        $response->assertSee('id="report-custom-filter-form"', false);

        // 4 Dropdown Pilihan
        $response->assertSee('id="filter-custom-day"', false);
        $response->assertSee('Semua Hari');
        $response->assertSee('Senin');
        $response->assertSee('Minggu');

        $response->assertSee('id="filter-custom-date"', false);
        $response->assertSee('Semua Tanggal');

        $response->assertSee('id="filter-custom-month"', false);
        $response->assertSee('Semua Bulan');
        $response->assertSee('Januari');
        $response->assertSee('Desember');

        $response->assertSee('id="filter-custom-year"', false);
        $response->assertSee('Semua Tahun');
        $response->assertSee('2026');
        $response->assertSee('2025');

        // Tombol Aksi: Batal | Terapkan (Tanpa Tombol Reset)
        $response->assertSee('id="btn-cancel-custom-date"', false);
        $response->assertSee('Batal');
        $response->assertSee('id="btn-apply-custom-date"', false);
        $response->assertSee('Terapkan');
        $response->assertDontSee('Reset');
    }
}
