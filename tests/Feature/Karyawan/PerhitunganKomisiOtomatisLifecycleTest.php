<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Modules\Karyawan\Services\KomisiKaryawanService;
use App\Modules\KasirPOS\Services\TransaksiService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerhitunganKomisiOtomatisLifecycleTest extends TestCase
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

        $this->branch = Branch::create(['name' => 'Cabang Tebet', 'address' => 'Jl. Tebet Raya', 'is_active' => true]);
        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch->id);

        $empUser = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee = Employee::create([
            'user_id' => $empUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);

        $this->category = ServiceCategory::create(['name' => 'Barbershop']);
    }

    public function test_sequence_a_advance_payment_does_not_generate_commission_until_service_completed(): void
    {
        $srv = $this->makeService('Paket Cukur Lengkap', 80000, CommissionType::Percentage->value, 20.00);

        $booking = Booking::create([
            'user_id' => $this->manager->id,
            'branch_id' => $this->branch->id,
            'service_id' => $srv->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-SEQ-A',
            'transaction_date' => now(),
            'subtotal' => 80000,
            'total' => 80000,
            'status' => StatusTransaksi::Paid,
        ]);
        $this->makeDetail($tx->id, $srv->id, 1, 80000, 80000);

        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);

        $booking->update(['status' => BookingStatus::Completed]);

        $this->assertDatabaseHas('employee_commissions', [
            'transaction_id' => $tx->id,
            'employee_id' => $this->employee->id,
            'commission_amount' => 16000.00,
            'status' => CommissionStatus::Pending->value,
        ]);
    }

    public function test_sequence_b_service_completed_first_does_not_generate_commission_until_payment_settled(): void
    {
        $srv = $this->makeService('Coloring Rambut', 120000, CommissionType::Fixed->value, 30000.00);

        $booking = Booking::create([
            'user_id' => $this->manager->id,
            'branch_id' => $this->branch->id,
            'service_id' => $srv->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '13:00',
            'status' => BookingStatus::Completed,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-SEQ-B',
            'transaction_date' => now(),
            'subtotal' => 120000,
            'total' => 120000,
            'status' => StatusTransaksi::Pending,
        ]);
        $this->makeDetail($tx->id, $srv->id, 1, 120000, 120000);

        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);

        $tx->update(['status' => StatusTransaksi::Paid]);

        $this->assertDatabaseHas('employee_commissions', [
            'transaction_id' => $tx->id,
            'employee_id' => $this->employee->id,
            'commission_amount' => 30000.00,
            'status' => CommissionStatus::Pending->value,
        ]);
    }

    public function test_repeated_processing_does_not_create_duplicate_commissions(): void
    {
        $srv = $this->makeService('Hair Spa', 100000, CommissionType::Percentage->value, 10.00);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'employee_id' => $this->employee->id,
            'transaction_code' => 'TRX-IDEMP-01',
            'transaction_date' => now(),
            'subtotal' => 100000,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
        ]);
        $this->makeDetail($tx->id, $srv->id, 1, 100000, 100000);

        $svc = app(KomisiKaryawanService::class);
        $res1 = $svc->processTransactionCommission($tx);
        $res2 = $svc->processTransactionCommission($tx);

        $this->assertCount(1, $res1);
        $this->assertCount(0, $res2);
        $this->assertEquals(1, EmployeeCommission::where('transaction_id', $tx->id)->count());
    }

    public function test_order_dropoff_completed_and_paid_triggers_automatic_commission(): void
    {
        $srv = $this->makeService('Cuci Lipat Kiloan', 10000, CommissionType::Fixed->value, 2000.00);

        $order = Order::create([
            'user_id' => $this->manager->id,
            'branch_id' => $this->branch->id,
            'service_id' => $srv->id,
            'employee_id' => $this->employee->id,
            'order_code' => 'ORD-TEST-01',
            'order_type' => 'drop_off',
            'estimated_quantity' => 5,
            'status' => StatusPesanan::Processing,
        ]);

        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->manager->id,
            'order_id' => $order->id,
            'transaction_code' => 'TRX-ORD-01',
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
        ]);
        $this->makeDetail($tx->id, $srv->id, 5, 10000, 50000);

        $this->assertDatabaseMissing('employee_commissions', ['transaction_id' => $tx->id]);

        $order->update(['status' => StatusPesanan::Completed]);

        $this->assertDatabaseHas('employee_commissions', [
            'transaction_id' => $tx->id,
            'employee_id' => $this->employee->id,
            'commission_amount' => 10000.00,
        ]);
    }

    public function test_pos_walkin_direct_transaction_triggers_commission(): void
    {
        $srv = $this->makeService('Cukur Express', 40000, CommissionType::Percentage->value, 25.00);

        $tx = app(TransaksiService::class)->createTransaction($this->manager, [
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Pelanggan Walk-In',
            'customer_phone' => '0899999999',
            'payment_method' => 'cash',
            'cash_received' => 50000,
            'items' => [['service_id' => $srv->id, 'qty' => 1]],
        ]);

        $this->assertEquals(StatusTransaksi::Paid, $tx->status);
        $this->assertDatabaseHas('employee_commissions', [
            'transaction_id' => $tx->id,
            'employee_id' => $this->employee->id,
            'commission_amount' => 10000.00,
            'status' => CommissionStatus::Pending->value,
        ]);
    }

    public function test_manual_commission_recording_remains_functional(): void
    {
        $srv = $this->makeService('Layanan Khusus', 100000, null, null);

        $res = $this->actingAs($this->manager)->post(route('employees.commissions.store'), [
            'employee_id' => $this->employee->id,
            'service_id' => $srv->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 20,
            'status' => CommissionStatus::Pending->value,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $this->employee->id,
            'service_id' => $srv->id,
            'commission_amount' => 20000.00,
            'status' => CommissionStatus::Pending->value,
        ]);
    }

    private function makeService(string $name, float $price, ?string $commType, ?float $commVal): Service
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

    private function makeDetail(int $txId, int $srvId, int $qty, float $price, float $subtotal): TransactionDetail
    {
        return TransactionDetail::create([
            'transaction_id' => $txId,
            'service_id' => $srvId,
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);
    }
}
