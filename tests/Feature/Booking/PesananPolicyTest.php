<?php

namespace Tests\Feature\Booking;

use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;

    protected Branch $branchB;

    protected Service $serviceA;

    protected Service $serviceB;

    protected User $owner;

    protected User $managerA;

    protected User $cashierA;

    protected User $employeeA;

    protected User $customer;

    protected Order $orderBranchA;

    protected Order $orderBranchB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branchA = Branch::create([
            'name' => 'Cabang A',
            'address' => 'Jl. A',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'name' => 'Cabang B',
            'address' => 'Jl. B',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Laundry', 'description' => 'Laundry']);

        $this->serviceA = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Laundry Kiloan A',
            'price' => 10000,
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $this->serviceB = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Laundry Kiloan B',
            'price' => 12000,
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);

        $this->orderBranchA = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchA->id,
            'service_id' => $this->serviceA->id,
            'status' => StatusPesanan::Pending,
        ]);

        $this->orderBranchB = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchB->id,
            'service_id' => $this->serviceB->id,
            'status' => StatusPesanan::Pending,
        ]);

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $this->managerA = User::factory()->create(['role_id' => $managerRole->id]);
        $this->managerA->branches()->attach($this->branchA->id);

        $this->cashierA = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashierA->branches()->attach($this->branchA->id);

        $this->employeeA = User::factory()->create(['role_id' => $employeeRole->id]);
        Employee::create([
            'user_id' => $this->employeeA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Staff Cabang A',
            'phone' => '08123456789',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_and_update_orders_from_any_branch(): void
    {
        $this->actingAs($this->owner)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertOk();

        $this->actingAs($this->owner)
            ->get(route('orders.show', $this->orderBranchB))
            ->assertOk();

        $this->actingAs($this->owner)
            ->patch(route('orders.update-status', $this->orderBranchB), [
                'status' => StatusPesanan::Received->value,
            ])
            ->assertSessionHas('success');
    }

    public function test_cashier_can_view_and_update_order_from_assigned_branch(): void
    {
        $this->actingAs($this->cashierA)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertOk();

        $response = $this->actingAs($this->cashierA)
            ->patch(route('orders.update-status', $this->orderBranchA), [
                'status' => StatusPesanan::Received->value,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(StatusPesanan::Received, $this->orderBranchA->fresh()->status);
    }

    public function test_cashier_is_forbidden_from_viewing_or_updating_order_from_other_branch(): void
    {
        $this->actingAs($this->cashierA)
            ->get(route('orders.show', $this->orderBranchB))
            ->assertForbidden();

        $this->actingAs($this->cashierA)
            ->patch(route('orders.update-status', $this->orderBranchB), [
                'status' => StatusPesanan::Received->value,
            ])
            ->assertForbidden();

        $this->assertEquals(StatusPesanan::Pending, $this->orderBranchB->fresh()->status);
    }

    public function test_employee_can_view_and_update_order_from_assigned_branch(): void
    {
        $this->actingAs($this->employeeA)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertOk();

        $response = $this->actingAs($this->employeeA)
            ->patch(route('orders.update-status', $this->orderBranchA), [
                'status' => StatusPesanan::Received->value,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(StatusPesanan::Received, $this->orderBranchA->fresh()->status);
    }

    public function test_employee_is_forbidden_from_viewing_or_updating_order_from_other_branch(): void
    {
        $this->actingAs($this->employeeA)
            ->get(route('orders.show', $this->orderBranchB))
            ->assertForbidden();

        $this->actingAs($this->employeeA)
            ->patch(route('orders.update-status', $this->orderBranchB), [
                'status' => StatusPesanan::Received->value,
            ])
            ->assertForbidden();

        $this->assertEquals(StatusPesanan::Pending, $this->orderBranchB->fresh()->status);
    }

    public function test_manager_can_access_own_branch_and_is_denied_for_other_branch(): void
    {
        $this->actingAs($this->managerA)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertOk();

        $this->actingAs($this->managerA)
            ->get(route('orders.show', $this->orderBranchB))
            ->assertForbidden();

        $this->actingAs($this->managerA)
            ->patch(route('orders.update-status', $this->orderBranchB), [
                'status' => StatusPesanan::Received->value,
            ])
            ->assertForbidden();
    }

    public function test_customer_can_only_view_own_order(): void
    {
        $otherCustomer = User::factory()->create([
            'role_id' => Role::where('name', UserRole::Customer->value)->firstOrFail()->id,
        ]);

        $this->actingAs($this->customer)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertOk();

        $this->actingAs($otherCustomer)
            ->get(route('orders.show', $this->orderBranchA))
            ->assertForbidden();
    }
}
