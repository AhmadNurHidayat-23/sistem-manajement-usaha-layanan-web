<?php

namespace Tests\Feature\Karyawan;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftDanKomisiKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $employeeUser;

    protected Branch $branch1;

    protected Employee $employee1;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Tebet',
            'address' => 'Jl. Tebet Raya No. 12',
            'phone' => '08123456781',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Tebet',
            'email' => 'manager.tebet@example.com',
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Budi Teknisi',
            'email' => 'budi@example.com',
        ]);

        $this->employee1 = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Santoso',
            'phone' => '0811111111',
            'status' => 'active',
        ]);

        $cat = ServiceCategory::create([
            'name' => 'Perawatan Mesin',
            'slug' => 'perawatan-mesin',
        ]);

        $this->service = Service::create([
            'service_category_id' => $cat->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Tune Up Mesin Injeksi',
            'price' => 200000,
            'service_type' => 'both',
            'is_active' => true,
        ]);
    }

    public function test_shift_scheduling_and_attendance_status_update(): void
    {
        $response = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_shifts', [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'start_time' => '08:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $shift = EmployeeShift::where('employee_id', $this->employee1->id)->first();

        $responseStatus = $this->actingAs($this->manager)->patch(route('employees.shifts.status', $shift), [
            'status' => StatusShiftKaryawan::Present->value,
        ]);

        $responseStatus->assertRedirect();
        $this->assertEquals(StatusShiftKaryawan::Present, $shift->fresh()->status);
    }

    public function test_commission_creation_with_percentage_and_approval_flow(): void
    {
        $response = $this->actingAs($this->manager)->post(route('employees.commissions.store'), [
            'employee_id' => $this->employee1->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'status' => CommissionStatus::Pending->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_commissions', [
            'employee_id' => $this->employee1->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_amount' => 20000,
            'status' => CommissionStatus::Pending->value,
        ]);

        $commission = EmployeeCommission::where('employee_id', $this->employee1->id)->first();

        $responseApproved = $this->actingAs($this->manager)->patch(route('employees.commissions.status', $commission), [
            'status' => CommissionStatus::Approved->value,
        ]);
        $responseApproved->assertRedirect();
        $this->assertEquals(CommissionStatus::Approved, $commission->fresh()->status);

        $responsePaid = $this->actingAs($this->manager)->patch(route('employees.commissions.status', $commission), [
            'status' => CommissionStatus::Paid->value,
        ]);
        $responsePaid->assertRedirect();
        $this->assertEquals(CommissionStatus::Paid, $commission->fresh()->status);
    }

    public function test_employee_self_service_routes(): void
    {
        $responseDashboard = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $responseDashboard->assertOk()->assertSee('Budi Santoso');

        $responseShifts = $this->actingAs($this->employeeUser)->get(route('employees.my-shifts'));
        $responseShifts->assertOk();

        $responseCommissions = $this->actingAs($this->employeeUser)->get(route('employees.my-commissions'));
        $responseCommissions->assertOk();
    }

    public function test_manager_can_access_commissions_history_tab_and_filter_by_date_range(): void
    {
        EmployeeCommission::create([
            'employee_id' => $this->employee1->id,
            'service_id' => $this->service->id,
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'commission_amount' => 20000,
            'status' => CommissionStatus::Pending,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->manager)->get(route('employees.commissions.index', [
            'tab' => 'history',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('Riwayat Komisi')
            ->assertSee('Skema Komisi Layanan')
            ->assertSee('Tune Up Mesin Injeksi')
            ->assertSee('Budi Santoso');
    }

    public function test_manager_can_access_service_schemes_tab_with_branch_scoping(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Depok',
            'address' => 'Jl. Margonda Raya',
            'phone' => '08123456782',
            'is_active' => true,
        ]);

        $otherService = Service::create([
            'service_category_id' => $this->service->service_category_id,
            'branch_id' => $otherBranch->id,
            'name' => 'Layanan Luar Cabang Manajer',
            'price' => 150000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 15000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->manager)->get(route('employees.commissions.index', [
            'tab' => 'schemes',
        ]));

        $response->assertOk()
            ->assertSee('Skema Komisi Layanan')
            ->assertSee('Atur Skema')
            ->assertSee($this->service->name)
            ->assertDontSee($otherService->name);
    }
}
