<?php

namespace Tests\Feature\Manager;

use App\Enums\StatusShiftKaryawan;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerRbacAndBranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $managerBranch1;

    protected User $managerBranch2;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        $roleManager = Role::firstOrCreate(['name' => 'manager']);
        $roleCashier = Role::firstOrCreate(['name' => 'cashier']);
        $roleCustomer = Role::firstOrCreate(['name' => 'customer']);

        $this->branch1 = Branch::create([
            'name' => 'Barbershop Pusat',
            'address' => 'Jl. Merdeka No. 10',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);
        $this->branch2 = Branch::create([
            'name' => 'Laundry Selatan',
            'address' => 'Jl. Sudirman No. 20',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $this->managerBranch1 = User::factory()->create(['role_id' => $roleManager->id]);
        $this->managerBranch1->branches()->attach($this->branch1->id);

        $this->managerBranch2 = User::factory()->create(['role_id' => $roleManager->id]);
        $this->managerBranch2->branches()->attach($this->branch2->id);

        $this->cashier = User::factory()->create(['role_id' => $roleCashier->id]);
        $this->cashier->branches()->attach($this->branch1->id);

        $this->customer = User::factory()->create(['role_id' => $roleCustomer->id]);
    }

    public function test_manager_cannot_access_cashier_or_customer_member_pages(): void
    {
        $this->actingAs($this->managerBranch1)
            ->get('/members')
            ->assertForbidden();

        $this->actingAs($this->managerBranch1)
            ->get('/members/create')
            ->assertForbidden();

        $this->actingAs($this->managerBranch1)
            ->get('/membership-packages')
            ->assertForbidden();

        $this->actingAs($this->managerBranch1)
            ->get('/my-membership')
            ->assertForbidden();
    }

    public function test_manager_cannot_access_customer_booking_create_or_voucher_catalog(): void
    {
        $this->actingAs($this->managerBranch1)
            ->get('/bookings/create')
            ->assertForbidden();

        $this->actingAs($this->managerBranch1)
            ->get(route('promotions.public'))
            ->assertForbidden();

        $this->actingAs($this->managerBranch1)
            ->get(route('customer.promo'))
            ->assertForbidden();
    }

    public function test_cashier_can_access_members_and_customer_can_access_my_membership(): void
    {
        $this->actingAs($this->cashier)
            ->get('/members')
            ->assertOk();

        $this->actingAs($this->customer)
            ->get('/my-membership')
            ->assertOk();
    }

    public function test_manager_cannot_modify_employee_shift_of_another_branch(): void
    {
        $employeeBranch2 = Employee::create([
            'branch_id' => $this->branch2->id,
            'name' => 'Staf Laundry',
            'phone' => '081299991111',
            'status' => 'active',
        ]);

        $shiftBranch2 = EmployeeShift::create([
            'employee_id' => $employeeBranch2->id,
            'branch_id' => $this->branch2->id,
            'shift_date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->managerBranch1)
            ->patch(route('employees.shifts.status', $shiftBranch2), [
                'status' => StatusShiftKaryawan::Present->value,
            ]);

        $response->assertForbidden();
    }

    public function test_manager_can_access_own_dashboard(): void
    {
        $this->actingAs($this->managerBranch1)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Manajer');
    }
}
