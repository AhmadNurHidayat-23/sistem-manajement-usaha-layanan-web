<?php

namespace Tests\Feature\Karyawan;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSkill;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected Branch $branch1;

    protected Employee $employee1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Tebet',
            'address' => 'Jl. Tebet Raya No. 12',
            'phone' => '08123456781',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Owner Bos',
            'email' => 'owner@example.com',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Tebet',
            'email' => 'manager.tebet@example.com',
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Budi Teknisi',
            'email' => 'budi@example.com',
        ]);

        $this->employee1 = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Santoso',
            'phone' => '0811111111',
            'status' => 'active',
        ]);

        EmployeeSkill::create([
            'employee_id' => $this->employee1->id,
            'skill_name' => 'Ganti Oli',
        ]);
    }

    public function test_owner_can_access_employees_index(): void
    {
        $response = $this->actingAs($this->owner)->get(route('employees.index'));

        $response->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_owner_can_create_employee_with_skills(): void
    {
        $response = $this->actingAs($this->owner)->post(route('employees.store'), [
            'name' => 'Agus Pratama',
            'phone' => '0833333333',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
            'skills' => 'Cuci Cepat, Detailing Interior, Tune Up',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employees', [
            'name' => 'Agus Pratama',
            'phone' => '0833333333',
            'branch_id' => $this->branch1->id,
        ]);

        $employee = Employee::where('name', 'Agus Pratama')->first();
        $this->assertCount(3, $employee->skills);
        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $employee->id,
            'skill_name' => 'Cuci Cepat',
        ]);
    }

    public function test_owner_can_update_employee_and_sync_skills(): void
    {
        $response = $this->actingAs($this->owner)->put(route('employees.update', $this->employee1), [
            'name' => 'Budi Santoso Updated',
            'phone' => '0811119999',
            'branch_id' => $this->branch1->id,
            'status' => 'active',
            'skills' => 'Ganti Oli, Spooring Balancing, Perawatan AC',
        ]);

        $response->assertRedirect(route('employees.show', $this->employee1));
        $this->assertDatabaseHas('employees', [
            'id' => $this->employee1->id,
            'name' => 'Budi Santoso Updated',
            'phone' => '0811119999',
        ]);

        $this->assertCount(3, $this->employee1->fresh()->skills);
        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $this->employee1->id,
            'skill_name' => 'Spooring Balancing',
        ]);
    }

    public function test_owner_and_manager_can_toggle_employee_status(): void
    {
        $response = $this->actingAs($this->manager)->post(route('employees.toggle-status', $this->employee1));

        $response->assertRedirect();
        $this->assertEquals('inactive', $this->employee1->fresh()->status);

        $response2 = $this->actingAs($this->manager)->post(route('employees.toggle-status', $this->employee1));
        $response2->assertRedirect();
        $this->assertEquals('active', $this->employee1->fresh()->status);
    }
}
