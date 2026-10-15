<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\Karyawan\KomisiKaryawanService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerhitunganKomisiOtomatisTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Branch $branch;

    protected Employee $employee;

    protected ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Tebet',
            'address' => 'Jl. Tebet Raya No. 12',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch->id);

        $employeeUser = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);

        $this->category = ServiceCategory::create(['name' => 'Salon']);
    }

    public function test_automatic_percentage_commission_calculated_correctly(): void
    {
        $service = $this->createService('Potong Rambut', 100000, CommissionType::Percentage->value, 15.00);
        $booking = $this->createBooking($service, $this->employee->id);
        $tx = $this->createTx($booking->id, null, 200000);
        $this->createTxDetail($tx->id, $service->id, 2, 100000, 200000);

        $commissions = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(1, $commissions);
        $comm = $commissions->first();
        $this->assertEquals($this->employee->id, $comm->employee_id);
        $this->assertEquals(CommissionType::Percentage, $comm->commission_type);
        $this->assertEquals(15.00, (float) $comm->commission_value);
        $this->assertEquals(30000.00, (float) $comm->commission_amount);
        $this->assertEquals(CommissionStatus::Pending, $comm->status);
    }

    public function test_automatic_fixed_commission_calculated_correctly(): void
    {
        $service = $this->createService('Creambath', 150000, CommissionType::Fixed->value, 25000.00);
        $booking = $this->createBooking($service, $this->employee->id);
        $tx = $this->createTx($booking->id, null, 450000);
        $this->createTxDetail($tx->id, $service->id, 3, 150000, 450000);

        $commissions = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(1, $commissions);
        $comm = $commissions->first();
        $this->assertEquals($this->employee->id, $comm->employee_id);
        $this->assertEquals(CommissionType::Fixed, $comm->commission_type);
        $this->assertEquals(25000.00, (float) $comm->commission_value);
        $this->assertEquals(75000.00, (float) $comm->commission_amount);
        $this->assertEquals(CommissionStatus::Pending, $comm->status);
    }

    public function test_multi_service_transaction_calculates_separate_commissions(): void
    {
        $srvA = $this->createService('Layanan A', 100000, CommissionType::Percentage->value, 10.00);
        $srvB = $this->createService('Layanan B', 50000, CommissionType::Fixed->value, 12000.00);

        $tx = $this->createTx(null, $this->employee->id, 200000);
        $this->createTxDetail($tx->id, $srvA->id, 1, 100000, 100000);
        $this->createTxDetail($tx->id, $srvB->id, 2, 50000, 100000);

        $commissions = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(2, $commissions);
        $commA = $commissions->firstWhere('service_id', $srvA->id);
        $commB = $commissions->firstWhere('service_id', $srvB->id);
        $this->assertEquals(10000.00, (float) $commA->commission_amount);
        $this->assertEquals(24000.00, (float) $commB->commission_amount);
    }

    public function test_service_with_null_commission_config_does_not_generate_commission(): void
    {
        $service = $this->createService('Tanpa Komisi', 50000, null, null);
        $tx = $this->createTx(null, $this->employee->id, 50000);
        $this->createTxDetail($tx->id, $service->id, 1, 50000, 50000);

        $commissions = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(0, $commissions);
        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);
    }

    public function test_transaction_without_employee_does_not_guess_or_assign_to_cashier(): void
    {
        $service = $this->createService('Pangkas', 50000, CommissionType::Percentage->value, 10.00);
        $tx = $this->createTx(null, null, 50000);
        $this->createTxDetail($tx->id, $service->id, 1, 50000, 50000);

        $commissions = app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $this->assertCount(0, $commissions);
        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);
    }

    public function test_master_service_rate_update_does_not_alter_historical_commission(): void
    {
        $service = $this->createService('Tarif Berubah', 100000, CommissionType::Percentage->value, 10.00);
        $tx = $this->createTx(null, $this->employee->id, 100000);
        $this->createTxDetail($tx->id, $service->id, 1, 100000, 100000);

        $comm = app(KomisiKaryawanService::class)->processTransactionCommission($tx)->first();

        $service->update(['commission_type' => CommissionType::Fixed->value, 'commission_value' => 50000.00]);

        $historical = $comm->fresh();
        $this->assertEquals(CommissionType::Percentage, $historical->commission_type);
        $this->assertEquals(10.00, (float) $historical->commission_value);
        $this->assertEquals(10000.00, (float) $historical->commission_amount);
    }

    public function test_branch_isolation_and_authorization_for_commissions(): void
    {
        $otherBranch = Branch::create(['name' => 'Cabang Bali', 'address' => 'Jl. Kuta', 'is_active' => true]);
        $otherManager = User::factory()->create(['role_id' => Role::where('name', UserRole::Manager->value)->first()?->id]);
        $otherManager->branches()->attach($otherBranch->id);

        $service = $this->createService('Potong Tebet', 50000, CommissionType::Fixed->value, 10000.00);
        $tx = $this->createTx(null, $this->employee->id, 50000);
        $this->createTxDetail($tx->id, $service->id, 1, 50000, 50000);

        app(KomisiKaryawanService::class)->processTransactionCommission($tx);

        $tebetComms = app(KomisiKaryawanService::class)->getCommissions($this->manager);
        $this->assertCount(1, $tebetComms);

        $baliComms = app(KomisiKaryawanService::class)->getCommissions($otherManager);
        $this->assertCount(0, $baliComms);
    }

    private function createService(string $name, float $price, ?string $commType, ?float $commVal): Service
    {
        return Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'name' => $name,
            'price' => $price,
            'service_type' => 'both',
            'commission_type' => $commType,
            'commission_value' => $commVal,
            'is_active' => true,
        ]);
    }

    private function createBooking(Service $service, int $employeeId): Booking
    {
        return Booking::create([
            'user_id' => $this->manager->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'employee_id' => $employeeId,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Completed,
        ]);
    }

    private function createTx(?int $bookingId, ?int $empId, float $total): Transaction
    {
        return Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'booking_id' => $bookingId,
            'employee_id' => $empId,
            'transaction_code' => 'TRX-'.uniqid(),
            'transaction_date' => now(),
            'subtotal' => $total,
            'total' => $total,
            'status' => StatusTransaksi::Paid,
        ]);
    }

    private function createTxDetail(int $txId, int $serviceId, int $qty, float $price, float $subtotal): TransactionDetail
    {
        return TransactionDetail::create([
            'transaction_id' => $txId,
            'service_id' => $serviceId,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);
    }
}
