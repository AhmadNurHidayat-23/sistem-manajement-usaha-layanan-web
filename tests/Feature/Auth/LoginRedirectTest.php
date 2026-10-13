<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_customer_can_register_and_is_assigned_customer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Customer',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Customer->value, $user->role->name);
    }

    public function test_owner_login_redirects_to_owner_dashboard(): void
    {
        $role = Role::where('name', UserRole::Owner->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_cashier_login_redirects_to_cashier_dashboard(): void
    {
        $role = Role::where('name', UserRole::Cashier->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('cashier.dashboard'));
    }

    public function test_cashier_login_ignores_public_landing_intended_url(): void
    {
        $role = Role::where('name', UserRole::Cashier->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->withSession(['url.intended' => url('/')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('cashier.dashboard'));
    }

    public function test_manager_login_redirects_to_manager_dashboard(): void
    {
        $role = Role::where('name', UserRole::Manager->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_employee_login_redirects_to_employee_dashboard(): void
    {
        $role = Role::where('name', UserRole::Employee->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_customer_login_redirects_to_customer_orders_area(): void
    {
        $role = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_customer_login_ignores_landing_page_intended_url(): void
    {
        $role = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->withSession(['url.intended' => url('/')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_authenticated_cashier_visiting_login_redirects_to_cashier_dashboard(): void
    {
        $role = Role::where('name', UserRole::Cashier->value)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('cashier.dashboard'));
    }
}
