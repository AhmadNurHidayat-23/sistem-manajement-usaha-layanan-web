<?php

namespace Tests\Feature\Karyawan;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $employeeUser;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employee1;

    protected Employee $employee2;

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

        $this->branch2 = Branch::create([
            'name' => 'Cabang Senopati',
            'address' => 'Jl. Senopati No. 45',
            'phone' => '08123456782',
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

        $otherEmployeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Siti Kasir-Staf',
            'email' => 'siti@example.com',
        ]);

        $this->employee1 = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Santoso',
            'phone' => '0811111111',
            'status' => 'active',
        ]);

        $this->employee2 = Employee::create([
            'user_id' => $otherEmployeeUser->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Rian Hidayat',
            'phone' => '0822222222',
            'status' => 'active',
        ]);
    }

    public function test_manager_only_sees_employees_in_managed_branch(): void
    {
        $response = $this->actingAs($this->manager)->get(route('employees.index'));

        $response->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Rian Hidayat');
    }

    public function test_manager_cannot_create_employee_for_unmanaged_branch(): void
    {
        $response = $this->actingAs($this->manager)->post(route('employees.store'), [
            'name' => 'Dani Senopati',
            'phone' => '0844444444',
            'branch_id' => $this->branch2->id,
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('employees', ['name' => 'Dani Senopati']);
    }

    public function test_manager_cannot_manage_employee_in_other_branch(): void
    {
        $response = $this->actingAs($this->manager)->get(route('employees.edit', $this->employee2));
        $response->assertForbidden();

        $responseUpdate = $this->actingAs($this->manager)->put(route('employees.update', $this->employee2), [
            'name' => 'Hacked Name',
            'phone' => '0800000000',
            'branch_id' => $this->branch2->id,
            'status' => 'active',
        ]);
        $responseUpdate->assertForbidden();
    }

    public function test_employee_can_view_own_profile_but_cannot_view_others(): void
    {
        $responseOwn = $this->actingAs($this->employeeUser)->get(route('employees.show', $this->employee1));
        $responseOwn->assertOk()->assertSee('Budi Santoso');

        $responseOther = $this->actingAs($this->employeeUser)->get(route('employees.show', $this->employee2));
        $responseOther->assertForbidden();
    }
}
