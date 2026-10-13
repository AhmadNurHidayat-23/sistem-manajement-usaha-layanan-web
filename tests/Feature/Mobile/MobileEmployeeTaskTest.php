<?php

namespace Tests\Feature\Mobile;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileEmployeeTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_employee_tasks_flow_and_isolation(): void
    {
        $branch = Branch::create(['name' => 'Cabang Karyawan', 'address' => 'Jl. Karyawan', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Teknis']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Perawatan Mesin',
            'price' => 150000,
            'service_type' => 'appointment',
            'estimated_duration' => 90,
            'is_active' => true,
        ]);

        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $userEmp1 = User::factory()->create(['role_id' => $employeeRole?->id, 'password' => Hash::make('password')]);
        $emp1 = Employee::create(['user_id' => $userEmp1->id, 'branch_id' => $branch->id, 'name' => 'Emp 1', 'phone' => '0811', 'status' => 'active']);

        $userEmp2 = User::factory()->create(['role_id' => $employeeRole?->id, 'password' => Hash::make('password')]);
        $emp2 = Employee::create(['user_id' => $userEmp2->id, 'branch_id' => $branch->id, 'name' => 'Emp 2', 'phone' => '0822', 'status' => 'active']);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $customerRole?->id]);

        $taskEmp1 = Booking::create([
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'employee_id' => $emp1->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $taskEmp2 = Booking::create([
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'employee_id' => $emp2->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Emp 1 login
        $login1 = $this->postJson('/api/mobile/login', ['email' => $userEmp1->email, 'password' => 'password']);
        $token1 = $login1->json('data.token');

        // Emp 1 task list only shows taskEmp1
        $emp1Tasks = $this->withHeader('Authorization', 'Bearer '.$token1)->getJson('/api/mobile/employee/tasks');
        $emp1Tasks->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $taskEmp1->id)
            ->assertJsonPath('data.0.service_name', 'Perawatan Mesin')
            ->assertJsonPath('data.0.duration_formatted', '±1,5 jam');

        // Emp 1 dashboard returns task_stats
        $dash = $this->withHeader('Authorization', 'Bearer '.$token1)->getJson('/api/mobile/employee/dashboard');
        $dash->assertStatus(200)
            ->assertJsonPath('data.task_stats.total', 1)
            ->assertJsonPath('data.task_stats.pending', 1);

        // Emp 1 transitions task from confirmed -> in_progress
        $startRes = $this->withHeader('Authorization', 'Bearer '.$token1)->patchJson("/api/mobile/employee/tasks/{$taskEmp1->id}/status", [
            'status' => 'in_progress',
        ]);
        $startRes->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        // Emp 1 transitions task from in_progress -> completed
        $doneRes = $this->withHeader('Authorization', 'Bearer '.$token1)->patchJson("/api/mobile/employee/tasks/{$taskEmp1->id}/status", [
            'status' => 'completed',
        ]);
        $doneRes->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        // Emp 1 attempts to access or modify Emp 2's task -> rejected 404
        $forbiddenShow = $this->withHeader('Authorization', 'Bearer '.$token1)->getJson("/api/mobile/employee/tasks/{$taskEmp2->id}");
        $forbiddenShow->assertStatus(404);

        $forbiddenPatch = $this->withHeader('Authorization', 'Bearer '.$token1)->patchJson("/api/mobile/employee/tasks/{$taskEmp2->id}/status", [
            'status' => 'in_progress',
        ]);
        $forbiddenPatch->assertStatus(404);
    }
}
