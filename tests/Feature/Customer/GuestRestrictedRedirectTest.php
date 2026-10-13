<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRestrictedRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Pratama',
            'email' => 'budi.pratama@example.com',
        ]);

        $this->branch = Branch::create([
            'name' => 'Laundry Express Sudirman',
            'address' => 'Jl. Sudirman No. 12',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Kiloan']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Kilat Express',
            'description' => 'Cuci higienis selesai dalam 3 jam.',
            'price' => 25000,
            'service_type' => 'dropoff',
            'estimated_duration' => 180,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_restricted_routes(): void
    {
        $response = $this->get(route('customer.pesanan.create'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('customer.pesanan.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('customer.member'));
        $response->assertRedirect(route('login'));

        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Pending,
            'order_number' => 'ORD-TEST-001',
            'total_amount' => 50000,
        ]);
        $response = $this->get(route('customer.pembayaran.show', $order));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('customer.ulasan.index'));
        $response->assertRedirect(route('login'));
    }
}
