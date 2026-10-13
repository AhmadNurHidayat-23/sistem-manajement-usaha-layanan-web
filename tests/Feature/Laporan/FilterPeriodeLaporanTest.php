<?php

namespace Tests\Feature\Laporan;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterPeriodeLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Barbershop & Studio Pusat',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Barbershop Cabang Timur',
            'address' => 'Jl. Pemuda No. 20',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner SIMAUL',
            'email' => 'owner.filter@simaul.test',
            'role_id' => $ownerRole->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Cabang Pusat',
            'email' => 'manager.filter@simaul.test',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->createTransactionsFixture();
    }

    protected function createTransactionsFixture(): void
    {
        // 1. Transaksi Hari Ini
        $trxToday = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-TODAY-01',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
        Payment::create([
            'transaction_id' => $trxToday->id,
            'payment_method' => 'cash',
            'amount' => 100000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        // 2. Transaksi 10 hari lalu
        $tenDaysAgo = now()->subDays(10);
        $trxPast = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-PAST-02',
            'subtotal' => 200000,
            'discount' => 0,
            'total' => 200000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => $tenDaysAgo,
        ]);
        Payment::create([
            'transaction_id' => $trxPast->id,
            'payment_method' => 'qris',
            'amount' => 200000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => $tenDaysAgo,
        ]);
    }

    public function test_filter_today_calculates_only_today_transactions(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'today',
            'branch_id' => $this->branch1->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['period'] === 'today'
                && $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 100000.0;
        });

        // Verifikasi tautan Hari Ini aktif dengan bg-[#062072] text-white
        $response->assertSee('period=today');
        $response->assertSee('id="report-custom-filter-form"', false);
    }

    public function test_filter_this_week_and_alias_week(): void
    {
        $responseWeek = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_week',
            'branch_id' => $this->branch1->id,
        ]));
        $responseWeek->assertOk();
        $responseWeek->assertViewHas('report', fn ($r) => $r['period'] === 'this_week');

        // Uji alias week
        $responseAlias = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'week',
            'branch_id' => $this->branch1->id,
        ]));
        $responseAlias->assertOk();
        $responseAlias->assertViewHas('report', fn ($r) => $r['period'] === 'this_week');
    }

    public function test_filter_this_month_and_this_year(): void
    {
        $responseMonth = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_month',
            'branch_id' => $this->branch1->id,
        ]));
        $responseMonth->assertOk();
        $responseMonth->assertViewHas('report', fn ($r) => $r['period'] === 'this_month');

        $responseYear = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_year',
            'branch_id' => $this->branch1->id,
        ]));
        $responseYear->assertOk();
        $responseYear->assertViewHas('report', fn ($r) => $r['period'] === 'this_year');
    }

    public function test_filter_custom_period_with_valid_dates(): void
    {
        $startDate = now()->subDays(12)->format('Y-m-d');
        $endDate = now()->subDays(8)->format('Y-m-d');

        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'branch_id' => $this->branch1->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) use ($startDate, $endDate) {
            return $report['period'] === 'custom'
                && $report['start_date'] === $startDate
                && $report['end_date'] === $endDate
                && $report['summary']['total_transactions'] === 1
                && (float) $report['summary']['total_omzet'] === 200000.0;
        });

        // Form kustom aktif dengan class flex
        $response->assertSee('class="flex flex-wrap items-center', false);
    }

    public function test_custom_period_rejects_end_date_before_start_date(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'custom',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-10',
        ]));

        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_preset_period_overrides_stale_dates_in_query(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'today',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'branch_id' => $this->branch1->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['period'] === 'today'
                && $report['summary']['total_transactions'] === 1;
        });
    }

    public function test_export_excel_and_pdf_inherits_active_period(): void
    {
        $responseExcel = $this->actingAs($this->owner)->get(route('reports.export.excel', [
            'period' => 'today',
            'branch_id' => $this->branch1->id,
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $responseExcel->headers->get('Content-Disposition'));

        $responsePdf = $this->actingAs($this->owner)->get(route('reports.export.pdf', [
            'period' => 'today',
            'branch_id' => $this->branch1->id,
        ]));
        $responsePdf->assertOk();
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());
    }

    public function test_manager_sees_compact_text_and_no_branch_select(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.index'));
        $response->assertOk();
        $response->assertSee('Cabang:');
        $response->assertSee('Barbershop &amp; Studio', false);
        $response->assertDontSee('id="report-branch-select"', false);
    }
}
