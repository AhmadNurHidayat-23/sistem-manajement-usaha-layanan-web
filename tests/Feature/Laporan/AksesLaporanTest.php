<?php

namespace Tests\Feature\Laporan;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Barat',
            'address' => 'Jl. Thamrin No. 2',
            'phone' => '0822222222',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@laporan.test',
            'role_id' => $ownerRole->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Pusat',
            'email' => 'manager@laporan.test',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir 1',
            'email' => 'cashier@laporan.test',
            'role_id' => $cashierRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan 1',
            'email' => 'customer@laporan.test',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_owner_can_view_comprehensive_report_for_all_branches(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index'));
        $response->assertOk();
        $response->assertViewIs('modules.laporan-keuangan.index');
        $response->assertSee('Laporan Kinerja');
        $response->assertSee('Semua Cabang (Laporan Gabungan)');
    }

    public function test_owner_can_filter_report_by_specific_branch(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'branch_id' => $this->branch1->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['branch_id'] === $this->branch1->id
                && ! $report['is_combined'];
        });
    }

    public function test_owner_can_filter_report_by_date_period(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'period' => 'this_month',
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['period'] === 'this_month';
        });
    }

    public function test_manager_can_view_report_for_assigned_branch(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.index'));
        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['branch_id'] === $this->branch1->id;
        });
    }

    public function test_manager_cannot_view_report_for_other_branches(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.index', [
            'branch_id' => $this->branch2->id,
        ]));

        $response->assertForbidden();
    }

    public function test_unauthorized_roles_cannot_access_reports(): void
    {
        $responseCashier = $this->actingAs($this->cashier)->get(route('reports.index'));
        $responseCashier->assertForbidden();

        $responseCustomer = $this->actingAs($this->customer)->get(route('reports.index'));
        $responseCustomer->assertForbidden();
    }

    public function test_manager_can_export_excel_and_pdf_for_assigned_branch(): void
    {
        $responseExcel = $this->actingAs($this->manager)->get(route('reports.export.excel', [
            'branch_id' => $this->branch1->id,
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $responseExcel->headers->get('Content-Disposition'));

        $responsePdf = $this->actingAs($this->manager)->get(route('reports.export.pdf', [
            'branch_id' => $this->branch1->id,
        ]));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $responsePdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());
    }

    public function test_manager_cannot_export_reports_for_unauthorized_branch(): void
    {
        $responseExcel = $this->actingAs($this->manager)->get(route('reports.export.excel', [
            'branch_id' => $this->branch2->id,
        ]));
        $responseExcel->assertForbidden();

        $responsePdf = $this->actingAs($this->manager)->get(route('reports.export.pdf', [
            'branch_id' => $this->branch2->id,
        ]));
        $responsePdf->assertForbidden();
    }

    public function test_manager_sees_branch_report_title_and_no_branch_select(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.index'));
        $response->assertOk();
        $response->assertSee('Laporan Keuangan Cabang');
        $response->assertSee('Ringkasan omzet, transaksi, dan kinerja keuangan cabang.');
        $response->assertDontSee('id="report-branch-select"', false);
    }

    public function test_owner_sees_business_report_title_and_branch_select(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.index'));
        $response->assertOk();
        $response->assertSee('Laporan Kinerja &amp; Keuangan Bisnis', false);
        $response->assertSee('id="report-branch-select"', false);
    }
}
