<?php

namespace Tests\Feature\Inventori;

use App\Enums\InventoryStockStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusDanPeringatanStokTest extends TestCase
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

    public function test_inventory_item_computes_stock_status_correctly(): void
    {
        $availableItem = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Bahan Tersedia',
            'unit' => 'pcs',
            'current_stock' => 10,
            'minimum_stock' => 5,
        ]);
        $this->assertEquals(InventoryStockStatus::Available->value, $availableItem->status);
        $this->assertEquals('Tersedia', $availableItem->status_label);

        $lowItem = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Bahan Menipis',
            'unit' => 'pcs',
            'current_stock' => 4,
            'minimum_stock' => 5,
        ]);
        $this->assertEquals(InventoryStockStatus::Low->value, $lowItem->status);
        $this->assertEquals('Menipis', $lowItem->status_label);

        $outItem = InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Bahan Habis',
            'unit' => 'pcs',
            'current_stock' => 0,
            'minimum_stock' => 5,
        ]);
        $this->assertEquals(InventoryStockStatus::OutOfStock->value, $outItem->status);
        $this->assertEquals('Habis', $outItem->status_label);
    }

    public function test_manager_dashboard_displays_inventory_stock_alert_summary(): void
    {
        InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Tonik Khusus Menipis',
            'unit' => 'botol',
            'current_stock' => 2,
            'minimum_stock' => 5,
        ]);

        InventoryItem::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Kapas Habis',
            'unit' => 'pack',
            'current_stock' => 0,
            'minimum_stock' => 3,
        ]);

        $response = $this->actingAs($this->manager1)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Peringatan Ketersediaan Stok Bahan');
        $response->assertSee('1 bahan menipis');
        $response->assertSee('1 bahan habis');
    }
}
