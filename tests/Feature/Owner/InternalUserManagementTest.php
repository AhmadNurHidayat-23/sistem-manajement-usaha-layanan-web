<?php

namespace Tests\Feature\Owner;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Role $managerRole;

    protected Role $cashierRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $this->managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $this->cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Alpha',
            'address' => 'Jl. Alpha No. 1',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Beta',
            'address' => 'Jl. Beta No. 2',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);
        $this->manager = User::factory()->create(['role_id' => $this->managerRole->id]);
        $this->cashier = User::factory()->create(['role_id' => $this->cashierRole->id]);
        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_non_owner_users_cannot_access_internal_user_management(): void
    {
        // 1. Guest redirected to login
        $this->get(route('owner.internal-users.index'))
            ->assertRedirect(route('login'));

        // 2. Customer forbidden
        $this->actingAs($this->customer)
            ->get(route('owner.internal-users.index'))
            ->assertForbidden();

        // 3. Cashier forbidden
        $this->actingAs($this->cashier)
            ->get(route('owner.internal-users.index'))
            ->assertForbidden();

        // 4. Manager forbidden
        $this->actingAs($this->manager)
            ->get(route('owner.internal-users.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_internal_users_index(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.internal-users.index'));

        $response->assertOk()
            ->assertSee('Manajemen Akun Internal')
            ->assertSee($this->owner->name)
            ->assertSee($this->manager->name)
            ->assertSee($this->cashier->name)
            ->assertDontSee($this->customer->email); // Customer is filtered out
    }

    public function test_owner_can_create_cashier_and_assign_multiple_branches(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.internal-users.store'), [
            'name' => 'Kasir Dua Cabang',
            'email' => 'kasir.multi@example.com',
            'phone' => '081299990001',
            'role_id' => $this->cashierRole->id,
            'branch_ids' => [$this->branch1->id, $this->branch2->id],
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('owner.internal-users.index'))
            ->assertSessionHas('success');

        $created = User::where('email', 'kasir.multi@example.com')->first();
        $this->assertNotNull($created);
        $this->assertEquals('Kasir Dua Cabang', $created->name);
        $this->assertEquals($this->cashierRole->id, $created->role_id);
        $this->assertCount(2, $created->branches);
        $this->assertTrue($created->branches->contains($this->branch1));
        $this->assertTrue($created->branches->contains($this->branch2));
    }

    public function test_owner_can_create_manager_account(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.internal-users.store'), [
            'name' => 'Manager Baru',
            'email' => 'manager.baru@example.com',
            'phone' => '081299990002',
            'role_id' => $this->managerRole->id,
            'branch_ids' => [$this->branch1->id],
            'password' => 'manager1234',
            'password_confirmation' => 'manager1234',
        ]);

        $response->assertRedirect(route('owner.internal-users.index'))
            ->assertSessionHas('success');

        $created = User::where('email', 'manager.baru@example.com')->first();
        $this->assertNotNull($created);
        $this->assertEquals($this->managerRole->id, $created->role_id);
        $this->assertCount(1, $created->branches);
    }

    public function test_validation_fails_when_role_is_invalid_or_branches_missing(): void
    {
        $customerRoleId = Role::where('name', UserRole::Customer->value)->firstOrFail()->id;

        // Role cannot be customer in internal user management
        $response = $this->actingAs($this->owner)->post(route('owner.internal-users.store'), [
            'name' => 'Akun Salah',
            'email' => 'salah.role@example.com',
            'role_id' => $customerRoleId,
            'branch_ids' => [$this->branch1->id],
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['role_id']);

        // Branch ids is required
        $resNoBranch = $this->actingAs($this->owner)->post(route('owner.internal-users.store'), [
            'name' => 'Tanpa Cabang',
            'email' => 'tanpa.cabang@example.com',
            'role_id' => $this->cashierRole->id,
            'branch_ids' => [],
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $resNoBranch->assertSessionHasErrors(['branch_ids']);
    }

    public function test_owner_can_edit_internal_user_and_update_branches(): void
    {
        $this->cashier->branches()->attach($this->branch1->id);

        $editView = $this->actingAs($this->owner)
            ->get(route('owner.internal-users.edit', $this->cashier));
        $editView->assertOk()
            ->assertSee($this->cashier->name);

        $response = $this->actingAs($this->owner)
            ->put(route('owner.internal-users.update', $this->cashier), [
                'name' => 'Kasir Updated',
                'email' => $this->cashier->email,
                'phone' => '0899887766',
                'role_id' => $this->cashierRole->id,
                'branch_ids' => [$this->branch2->id], // Switch from branch 1 to branch 2
            ]);

        $response->assertRedirect(route('owner.internal-users.index'))
            ->assertSessionHas('success');

        $this->cashier->refresh();
        $this->assertEquals('Kasir Updated', $this->cashier->name);
        $this->assertEquals('0899887766', $this->cashier->phone);
        $this->assertCount(1, $this->cashier->branches);
        $this->assertEquals($this->branch2->id, $this->cashier->branches->first()->id);
    }

    public function test_editing_customer_via_internal_user_module_returns_404(): void
    {
        $this->actingAs($this->owner)
            ->get(route('owner.internal-users.edit', $this->customer))
            ->assertNotFound();
    }
}
