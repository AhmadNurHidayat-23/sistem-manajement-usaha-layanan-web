<?php

namespace Tests\Feature\Cabang;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_owner_can_view_branch_list_and_create_new_branch(): void
    {
        $role = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($owner)->get('/branches');
        $response->assertStatus(200);

        $createResponse = $this->actingAs($owner)->post('/branches', [
            'name' => 'Cabang Baru Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '08123456789',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 10,
            'is_active' => 1,
        ]);

        $createResponse->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'name' => 'Cabang Baru Test',
            'max_slot_capacity' => 10,
        ]);
    }

    public function test_owner_can_toggle_branch_active_status(): void
    {
        $role = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $branch = Branch::create([
            'name' => 'Cabang Active Test',
            'address' => 'Alamat Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->patch("/branches/{$branch->id}/toggle-status");
        $response->assertRedirect();

        $branch->refresh();
        $this->assertFalse($branch->is_active);
    }

    public function test_manager_can_only_access_assigned_branch(): void
    {
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $manager = User::factory()->create(['role_id' => $managerRole->id]);

        $assignedBranch = Branch::create(['name' => 'Cabang Assigned', 'address' => 'Alamat A']);
        $unassignedBranch = Branch::create(['name' => 'Cabang Unassigned', 'address' => 'Alamat B']);

        $manager->branches()->attach($assignedBranch->id);

        $responseAssigned = $this->actingAs($manager)->get("/branches/{$assignedBranch->id}");
        $responseAssigned->assertStatus(200);

        $responseUnassigned = $this->actingAs($manager)->get("/branches/{$unassignedBranch->id}");
        $responseUnassigned->assertStatus(403);
    }

    public function test_cashier_cannot_access_branch_management(): void
    {
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $cashier = User::factory()->create(['role_id' => $cashierRole->id]);

        $response = $this->actingAs($cashier)->get('/branches');
        $response->assertStatus(403);
    }

    public function test_owner_can_view_branch_edit_and_update(): void
    {
        $role = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $role->id]);
        $branch = Branch::create([
            'name' => 'Cabang Edit Test',
            'address' => 'Alamat Awal',
            'is_active' => true,
        ]);

        $responseEdit = $this->actingAs($owner)->get(route('branches.edit', $branch));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Cabang Edit Test');

        $responseUpdate = $this->actingAs($owner)->put(route('branches.update', $branch), [
            'name' => 'Cabang Edit Test Renamed',
            'address' => 'Alamat Baru',
            'phone' => '0899999999',
            'service_mode' => 'both',
            'max_slot_capacity' => 15,
            'is_active' => 1,
        ]);
        $responseUpdate->assertRedirect(route('branches.show', $branch));
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Cabang Edit Test Renamed',
            'max_slot_capacity' => 15,
        ]);
    }
}
