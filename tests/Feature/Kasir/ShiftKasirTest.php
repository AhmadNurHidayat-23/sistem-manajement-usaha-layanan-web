<?php

namespace Tests\Feature\Kasir;

use App\Enums\CashierShiftStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftKasirTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Jendral Sudirman No. 10',
            'phone' => '08123456789',
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir Branch 1',
        ]);
        $this->cashier->branches()->attach($this->branch1->id);
    }

    public function test_cashier_can_open_and_close_shift(): void
    {
        $responseOpen = $this->actingAs($this->cashier)->post(route('pos.shift.open'), [
            'branch_id' => $this->branch1->id,
            'opening_cash' => 100000,
        ]);

        $responseOpen->assertRedirect(route('pos.index', ['branch_id' => $this->branch1->id]));
        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch1->id,
            'opening_cash' => 100000,
            'status' => CashierShiftStatus::Open->value,
        ]);

        $shift = CashierShift::where('user_id', $this->cashier->id)->first();

        $responseClose = $this->actingAs($this->cashier)->post(route('pos.shift.close'), [
            'branch_id' => $this->branch1->id,
            'closing_cash' => 150000,
            'notes' => 'Kas penutupan sesuai',
        ]);

        $responseClose->assertRedirect(route('pos.index'));
        $shift->refresh();
        $this->assertEquals(CashierShiftStatus::Closed, $shift->status);
        $this->assertEquals(150000, $shift->closing_cash);
        $this->assertNotNull($shift->closed_at);
    }

    public function test_cashier_cannot_open_duplicate_shift(): void
    {
        CashierShift::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch1->id,
            'opened_at' => now(),
            'opening_cash' => 50000,
            'status' => CashierShiftStatus::Open,
        ]);

        $response = $this->actingAs($this->cashier)->post(route('pos.shift.open'), [
            'branch_id' => $this->branch1->id,
            'opening_cash' => 50000,
        ]);

        $response->assertSessionHasErrors('shift');
    }
}
