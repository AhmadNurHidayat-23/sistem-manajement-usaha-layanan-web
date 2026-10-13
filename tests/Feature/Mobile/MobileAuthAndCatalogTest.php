<?php

namespace Tests\Feature\Mobile;

use App\Enums\UserRole;
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

class MobileAuthAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_customer_can_login_and_receive_token(): void
    {
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create([
            'role_id' => $customerRole?->id,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $token = $response->json('data.token');

        // Test me endpoint with bearer token
        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id);

        // Test customer cannot access employee endpoints
        $forbiddenResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/employee/tasks');

        $forbiddenResponse->assertStatus(403);

        // Test logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/logout');

        $logoutResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // After logout, token is revoked
        $meAfterLogout = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/me');

        $meAfterLogout->assertStatus(401);
    }

    public function test_employee_can_login_and_access_employee_dashboard(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test 1',
            'is_active' => true,
        ]);

        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $user = User::factory()->create([
            'role_id' => $employeeRole?->id,
            'password' => Hash::make('password123'),
        ]);

        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'name' => $user->name,
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'employee');

        $token = $response->json('data.token');

        // Employee can access employee dashboard
        $dashResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/employee/dashboard');

        $dashResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_manager_or_other_roles_cannot_login_to_mobile(): void
    {
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $user = User::factory()->create([
            'role_id' => $managerRole?->id,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_public_branches_and_services_accessible_without_auth(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Publik',
            'address' => 'Jl. Publik',
            'is_active' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Kategori Publik']);
        Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Publik',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);

        $branchesResponse = $this->getJson('/api/mobile/branches');
        $branchesResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $servicesResponse = $this->getJson('/api/mobile/services');
        $servicesResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.duration_formatted', '±1 jam');
    }
}
