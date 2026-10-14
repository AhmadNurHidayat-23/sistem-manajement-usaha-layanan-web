<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutDanRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_cashier_cannot_access_owner_dashboard(): void
    {
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $user = User::factory()->create([
            'role_id' => $cashierRole->id,
        ]);

        $response = $this->actingAs($user)->get('/owner/dashboard');

        $response->assertStatus(403);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_all_roles_logout_redirect_to_public_landing_page(): void
    {
        $roles = [
            UserRole::Customer->value,
            UserRole::Cashier->value,
            UserRole::Employee->value,
            UserRole::Manager->value,
            UserRole::Owner->value,
        ];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $user = User::factory()->create(['role_id' => $role->id]);

            $response = $this->actingAs($user)->post('/logout');

            $response->assertRedirect('/');
            $this->assertGuest();

            $landing = $this->get('/');
            $landing->assertStatus(200);
            $landing->assertSee('Login');
        }
    }

    public function test_all_demo_role_accounts_redirect_to_their_designated_dashboards(): void
    {
        $this->seed(DemoSeeder::class);

        $matrix = [
            'owner@simaul.com' => route('owner.dashboard'),
            'manajer@simaul.com' => route('manager.dashboard'),
            'kasir@simaul.com' => route('cashier.dashboard'),
            'kasir.andi@simaul.com' => route('cashier.dashboard'),
            'karyawan@simaul.com' => route('employee.dashboard'),
            'pelanggan@simaul.com' => route('customer.dashboard'),
            'cashier@example.com' => route('cashier.dashboard'),
            'owner@example.com' => route('owner.dashboard'),
            'manager@example.com' => route('manager.dashboard'),
            'employee@example.com' => route('employee.dashboard'),
            'customer@example.com' => route('customer.dashboard'),
        ];

        foreach ($matrix as $email => $expectedRedirect) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password123',
            ]);

            $response->assertRedirect($expectedRedirect);

            $followed = $this->get($expectedRedirect);
            $this->assertContains($followed->getStatusCode(), [200, 302]);

            $this->post('/logout');
        }
    }
}
