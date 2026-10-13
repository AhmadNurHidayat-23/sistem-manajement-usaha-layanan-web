<?php

namespace Tests\Feature\Laporan;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class KalkulasiDanEksporLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Branch $branch1;

    protected Service $service1;

    protected Service $service2;

    protected Employee $employee1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@laporan.test',
            'role_id' => $ownerRole->id,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Servis Mesin',
            'branch_id' => $this->branch1->id,
        ]);

        $this->service1 = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Ganti Oli Premium',
            'price' => 150000,
            'estimated_duration' => 30,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $this->service2 = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Tune Up Injeksi',
            'price' => 250000,
            'estimated_duration' => 60,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $this->employee1 = Employee::create([
            'user_id' => $this->owner->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Mekanik',
            'phone' => '0899999999',
            'status' => 'active',
        ]);
    }

    public function test_report_calculates_omzet_and_breakdowns_accurately(): void
    {
        $trx1 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-REP-01',
            'subtotal' => 150000,
            'discount' => 10000,
            'total' => 140000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx1->id,
            'service_id' => $this->service1->id,
            'qty' => 1,
            'price' => 150000,
            'subtotal' => 150000,
        ]);

        Payment::create([
            'transaction_id' => $trx1->id,
            'payment_method' => 'cash',
            'amount' => 140000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        $trx2 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->owner->id,
            'transaction_code' => 'TRX-REP-02',
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx2->id,
            'service_id' => $this->service2->id,
            'qty' => 1,
            'price' => 250000,
            'subtotal' => 250000,
        ]);

        Payment::create([
            'transaction_id' => $trx2->id,
            'payment_method' => 'qris',
            'amount' => 250000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        EmployeeCommission::create([
            'employee_id' => $this->employee1->id,
            'transaction_id' => $trx1->id,
            'service_id' => $this->service1->id,
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 20000,
            'commission_amount' => 20000,
            'status' => CommissionStatus::Paid,
        ]);

        $response = $this->actingAs($this->owner)->get(route('reports.index', [
            'branch_id' => $this->branch1->id,
            'period' => 'today',
        ]));

        $response->assertOk();
        $response->assertViewHas('report', function ($report) {
            return $report['summary']['total_transactions'] === 2
                && (float) $report['summary']['total_omzet'] === 390000.0
                && (float) $report['summary']['gross_omzet'] === 400000.0
                && (float) $report['summary']['total_discount'] === 10000.0
                && count($report['services']) === 2
                && count($report['payment_methods']) === 2
                && (float) $report['commissions']['total_commission'] === 20000.0;
        });
    }

    public function test_owner_can_export_excel_xlsx(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.export.excel', [
            'period' => 'this_month',
        ]));

        $response->assertOk();

        // 1. Content-Type sesuai XLSX
        $contentType = (string) $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $contentType);

        // 2. Nama file berakhiran .xlsx
        $contentDisposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('.xlsx', $contentDisposition);

        // 3. Konten file benar-benar format XLSX (format ZIP terkompresi dengan signature PK, bukan teks CSV)
        $content = $response->streamedContent();
        $this->assertStringStartsWith("PK\x03\x04", $content);

        // 4. File dapat dibaca oleh PhpSpreadsheet
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_uat_');
        file_put_contents($tempFile, $content);
        $spreadsheet = IOFactory::load($tempFile);
        $this->assertNotNull($spreadsheet->getActiveSheet());
        $this->assertEquals('Laporan Transaksi', $spreadsheet->getActiveSheet()->getTitle());
        @unlink($tempFile);
    }

    public function test_owner_can_export_pdf_download(): void
    {
        $response = $this->actingAs($this->owner)->get(route('reports.export.pdf', [
            'period' => 'this_month',
        ]));

        $response->assertOk();

        // 1. Content-Type application/pdf
        $contentType = (string) $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType);

        // 2. Nama file berakhiran .pdf
        $contentDisposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('.pdf', $contentDisposition);

        // 3. Response diawali signature standar file PDF
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF', $content);
    }
}
