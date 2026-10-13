<?php

namespace Tests\Feature\Inventori;

use App\Enums\InventoryMutationType;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MutasiInventoriTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager1;

    protected Branch $branch1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->manager1 = User::factory()->create([
            'name' => 'Manager Sudirman',
            'role_id' => $managerRole->id,
        ]);
        $this->manager1->branches()->attach($this->branch1->id);
    }

    public function test_manager_can_create_new_inventory_item_with_initial_stock(): void
    {
        $response = $this->actingAs($this->manager1)->post(route('inventori.store'), [
            'code' => 'BHN-101',
            'name' => 'Krim Creambath Aloe',
            'unit' => 'pot',
            'initial_stock' => 15,
            'minimum_stock' => 5,
            'cost_per_unit' => 35000,
            'description' => 'Untuk perawatan rambut rontok',
        ]);

        $item = InventoryItem::where('code', 'BHN-101')->first();
        $this->assertNotNull($item);
        $this->assertEquals($this->branch1->id, $item->branch_id);
        $this->assertEquals(15, (float) $item->current_stock);
        $this->assertEquals(5, (float) $item->minimum_stock);

        $response->assertRedirect(route('inventori.show', $item));

        $mutation = InventoryMutation::where('inventory_item_id', $item->id)->first();
        $this->assertNotNull($mutation);
        $this->assertEquals(InventoryMutationType::In, $mutation->type);
        $this->assertEquals(15, (float) $mutation->quantity);
        $this->assertEquals('initial_stock', $mutation->reference_type);
    }

    public function test_manager_can_record_stock_in(): void
    {
        $item = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'code' => 'BHN-201',
            'name' => 'Serum Vitamin Rambut',
            'unit' => 'ampul',
            'current_stock' => 10,
            'minimum_stock' => 5,
        ]);

        $response = $this->actingAs($this->manager1)->post(route('inventori.stock-in', $item), [
            'quantity' => 20,
            'reference_type' => 'restock',
            'notes' => 'Pengadaan dari Supplier Sejahtera',
        ]);

        $response->assertRedirect(route('inventori.show', $item));

        $item->refresh();
        $this->assertEquals(30, (float) $item->current_stock);

        $mutation = InventoryMutation::where('inventory_item_id', $item->id)->latest('id')->first();
        $this->assertEquals(InventoryMutationType::In, $mutation->type);
        $this->assertEquals(20, (float) $mutation->quantity);
        $this->assertEquals(10, (float) $mutation->stock_before);
        $this->assertEquals(30, (float) $mutation->stock_after);
    }

    public function test_manager_can_record_stock_out(): void
    {
        $item = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'code' => 'BHN-301',
            'name' => 'Bleaching Powder',
            'unit' => 'box',
            'current_stock' => 15,
            'minimum_stock' => 5,
        ]);

        $response = $this->actingAs($this->manager1)->post(route('inventori.stock-out', $item), [
            'quantity' => 4,
            'reference_type' => 'service_usage',
            'notes' => 'Pemakaian hair coloring',
        ]);

        $response->assertRedirect(route('inventori.show', $item));

        $item->refresh();
        $this->assertEquals(11, (float) $item->current_stock);

        $mutation = InventoryMutation::where('inventory_item_id', $item->id)->latest('id')->first();
        $this->assertEquals(InventoryMutationType::Out, $mutation->type);
        $this->assertEquals(4, (float) $mutation->quantity);
        $this->assertEquals(15, (float) $mutation->stock_before);
        $this->assertEquals(11, (float) $mutation->stock_after);
    }

    public function test_stock_out_fails_if_exceeding_current_stock(): void
    {
        $item = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'code' => 'BHN-401',
            'name' => 'Masker Wajah Emas',
            'unit' => 'lembar',
            'current_stock' => 5,
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($this->manager1)->post(route('inventori.stock-out', $item), [
            'quantity' => 10,
            'reference_type' => 'service_usage',
        ]);

        $response->assertSessionHasErrors();
        $item->refresh();
        $this->assertEquals(5, (float) $item->current_stock);
    }
}
