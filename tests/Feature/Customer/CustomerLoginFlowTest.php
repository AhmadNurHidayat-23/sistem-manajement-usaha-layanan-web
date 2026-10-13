<?php

namespace Tests\Feature\Customer;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLoginFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_customer_login_redirects_directly_to_customer_dashboard_area(): void
    {
        $response = $this->post('/login', [
            'email' => $this->customer->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($this->customer);

        // Follow redirect to verify no 403 / 404 / 500
        $followed = $this->get(route('customer.dashboard'));
        $followed->assertStatus(200);
        $followed->assertSee('Dashboard Pelanggan');
    }

    public function test_customer_login_clears_landing_page_intended_url_and_goes_to_customer_area(): void
    {
        $response = $this->withSession(['url.intended' => url('/')])
            ->post('/login', [
                'email' => $this->customer->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_customer_can_navigate_back_to_landing_page_via_beranda(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.home'));

        $response->assertStatus(200);
        $response->assertSee('SIMAUL');
    }

    public function test_authenticated_customer_visiting_login_redirects_to_customer_area(): void
    {
        $response = $this->actingAs($this->customer)->get('/login');

        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_customer_logout_returns_to_landing_page_and_guest_can_access_landing_page(): void
    {
        $this->actingAs($this->customer);

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee('Login');
    }
}
