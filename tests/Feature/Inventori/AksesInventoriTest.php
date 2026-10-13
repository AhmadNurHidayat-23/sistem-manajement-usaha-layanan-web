<?php

namespace Tests\Feature\Inventori;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesInventoriTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager1;

    protected User $manager2;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Thamrin',
            'address' => 'Jl. Thamrin No. 2',
            'phone' => '0822222222',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Bisnis',
            'role_id' => $ownerRole->id,
        ]);

        $this->manager1 = User::factory()->create([
            'name' => 'Manager Sudirman',
            'role_id' => $managerRole->id,
        ]);
        $this->manager1->branches()->attach($this->branch1->id);

        $this->manager2 = User::factory()->create([
            'name' => 'Manager Thamrin',
            'role_id' => $managerRole->id,
        ]);
        $this->manager2->branches()->attach($this->branch2->id);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Siti',
            'role_id' => $cashierRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Budi Customer',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_owner_can_see_inventory_from_all_branches(): void
    {
        InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'code' => 'BHN-001',
            'name' => 'Shampoo Herbal Cabang 1',
            'unit' => 'botol',
            'current_stock' => 20,
            'minimum_stock' => 5,
        ]);

        InventoryItem::create([
            'branch_id' => $this->branch2->id,
            'code' => 'BHN-002',
            'name' => 'Minyak Pijat Cabang 2',
            'unit' => 'liter',
            'current_stock' => 10,
            'minimum_stock' => 3,
        ]);

        $response = $this->actingAs($this->owner)->get(route('inventori.index'));

        $response->assertStatus(200);
        $response->assertSee('Shampoo Herbal Cabang 1');
        $response->assertSee('Minyak Pijat Cabang 2');
    }

    public function test_manager_can_only_see_their_branch_inventory(): void
    {
        InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'code' => 'BHN-001',
            'name' => 'Shampoo Herbal Cabang 1',
            'unit' => 'botol',
            'current_stock' => 20,
            'minimum_stock' => 5,
        ]);

        InventoryItem::create([
            'branch_id' => $this->branch2->id,
            'code' => 'BHN-002',
            'name' => 'Minyak Pijat Cabang 2',
            'unit' => 'liter',
            'current_stock' => 10,
            'minimum_stock' => 3,
        ]);

        $response = $this->actingAs($this->manager1)->get(route('inventori.index'));

        $response->assertStatus(200);
        $response->assertSee('Shampoo Herbal Cabang 1');
        $response->assertDontSee('Minyak Pijat Cabang 2');
    }

    public function test_manager_cannot_access_other_branch_inventory_item(): void
    {
        $item2 = InventoryItem::create([
            'branch_id' => $this->branch2->id,
            'code' => 'BHN-002',
            'name' => 'Minyak Pijat Cabang 2',
            'unit' => 'liter',
            'current_stock' => 10,
            'minimum_stock' => 3,
        ]);

        $this->actingAs($this->manager1)->get(route('inventori.show', $item2))->assertStatus(403);
        $this->actingAs($this->manager1)->get(route('inventori.edit', $item2))->assertStatus(403);
        $this->actingAs($this->manager1)->post(route('inventori.stock-in', $item2), ['quantity' => 5])->assertStatus(403);
    }

    public function test_unauthorized_roles_cannot_access_inventory(): void
    {
        $this->actingAs($this->cashier)->get(route('inventori.index'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('inventori.index'))->assertStatus(403);
    }
}
