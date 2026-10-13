<?php

namespace Tests\Feature\Manager;

use App\Enums\PromotionStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerRoleSrsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $managerBarbershop;

    protected User $managerLaundry;

    protected User $managerAutocare;

    protected Branch $branchBarbershop;

    protected Branch $branchLaundry;

    protected Branch $branchAutocare;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();

        $this->branchBarbershop = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Pusat Barbershop No. 1',
            'phone' => '0811111111',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        $this->branchLaundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. Selatan Laundry No. 2',
            'phone' => '0822222222',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $this->branchAutocare = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Barat Auto Care No. 3',
            'phone' => '0833333333',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Pak Owner',
            'email' => 'owner@simaul.com',
            'role_id' => $ownerRole->id,
        ]);

        $this->managerBarbershop = User::factory()->create([
            'name' => 'Siti Barbershop',
            'email' => 'manager.barbershop@simaul.com',
            'role_id' => $managerRole->id,
        ]);
        $this->managerBarbershop->branches()->attach($this->branchBarbershop->id);

        $this->managerLaundry = User::factory()->create([
            'name' => 'Hendro Laundry',
            'email' => 'manager.laundry@simaul.com',
            'role_id' => $managerRole->id,
        ]);
        $this->managerLaundry->branches()->attach($this->branchLaundry->id);

        $this->managerAutocare = User::factory()->create([
            'name' => 'Ratna Autocare',
            'email' => 'manager.autocare@simaul.com',
            'role_id' => $managerRole->id,
        ]);
        $this->managerAutocare->branches()->attach($this->branchAutocare->id);
    }

    public function test_manager_dashboards_show_correct_business_names_and_kpis(): void
    {
        // 1. Barbershop (Appointment only): Nama baku tanpa SIMAUL dan tanpa akhiran cabang
        $res1 = $this->actingAs($this->managerBarbershop)->get(route('manager.dashboard'));
        $res1->assertStatus(200);
        $res1->assertSee('Barbershop & Studio');
        $res1->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');

        // 2. Laundry (Drop-off only): Menampilkan "Tidak berlaku" untuk kehadiran booking
        $res2 = $this->actingAs($this->managerLaundry)->get(route('manager.dashboard'));
        $res2->assertStatus(200);
        $res2->assertSee('Laundry & Shoe Care');
        $res2->assertSee('Tidak berlaku');

        // 3. Auto Care (Both)
        $res3 = $this->actingAs($this->managerAutocare)->get(route('manager.dashboard'));
        $res3->assertStatus(200);
        $res3->assertSee('Auto Care & Detailing');
    }

    public function test_branch_isolation_on_financial_reports_and_exports(): void
    {
        // Manager Barbershop (branch 1) tidak boleh mengakses laporan branch 2
        $res = $this->actingAs($this->managerBarbershop)->get(route('reports.index', ['branch_id' => $this->branchLaundry->id]));
        $res->assertStatus(403);

        // Manager Laundry (branch 2) tidak boleh export Excel branch 1
        $resExcel = $this->actingAs($this->managerLaundry)->get(route('reports.export.excel', ['branch_id' => $this->branchBarbershop->id]));
        $resExcel->assertStatus(403);

        // Manager Laundry boleh export cabangnya sendiri
        $resOwnExcel = $this->actingAs($this->managerLaundry)->get(route('reports.export.excel', ['branch_id' => $this->branchLaundry->id]));
        $resOwnExcel->assertStatus(200);

        // Manager Laundry boleh export PDF cabangnya sendiri
        $resOwnPdf = $this->actingAs($this->managerLaundry)->get(route('reports.export.pdf', ['branch_id' => $this->branchLaundry->id]));
        $resOwnPdf->assertStatus(200);
    }

    public function test_shift_management_isolation_and_max_8_hours(): void
    {
        $empBarber = Employee::create([
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Budi Stylist',
            'phone' => '0812345671',
            'status' => 'active',
            'joined_at' => Carbon::now(),
        ]);

        $empLaundry = Employee::create([
            'branch_id' => $this->branchLaundry->id,
            'name' => 'Slamet Washer',
            'phone' => '0812345672',
            'status' => 'active',
            'joined_at' => Carbon::now(),
        ]);

        // Manager Barbershop tidak boleh membuat shift untuk karyawan cabang Laundry (403)
        $this->actingAs($this->managerBarbershop)->post(route('employees.shifts.store'), [
            'employee_id' => $empLaundry->id,
            'branch_id' => $this->branchLaundry->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ])->assertStatus(403);

        // Shift lebih dari 8 jam (08:00 - 17:00 = 9 jam) ditolak validasi
        $this->actingAs($this->managerBarbershop)->post(route('employees.shifts.store'), [
            'employee_id' => $empBarber->id,
            'branch_id' => $this->branchBarbershop->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
        ])->assertSessionHasErrors('end_time');

        // Shift valid 8 jam (09:00 - 17:00 = 8 jam) berhasil
        $this->actingAs($this->managerBarbershop)->post(route('employees.shifts.store'), [
            'employee_id' => $empBarber->id,
            'branch_id' => $this->branchBarbershop->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ])->assertSessionHas('success');
    }

    public function test_inventory_isolation_and_stock_operations(): void
    {
        $itemBarber = InventoryItem::create([
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Shampoo Salon',
            'unit' => 'Botol',
            'current_stock' => 10,
            'minimum_stock' => 3,
        ]);

        $itemLaundry = InventoryItem::create([
            'branch_id' => $this->branchLaundry->id,
            'name' => 'Deterjen Laundry',
            'unit' => 'Jerigen',
            'current_stock' => 5,
            'minimum_stock' => 2,
        ]);

        // Manager Barbershop tidak boleh stok masuk bahan cabang Laundry
        $this->actingAs($this->managerBarbershop)->post(route('inventori.stock-in', $itemLaundry), [
            'quantity' => 5,
            'notes' => 'Coba inject stok',
        ])->assertStatus(403);

        // Manager Barbershop sukses stok masuk bahan cabangnya
        $this->actingAs($this->managerBarbershop)->post(route('inventori.stock-in', $itemBarber), [
            'quantity' => 5,
            'notes' => 'Restock bahan',
        ])->assertRedirect(route('inventori.show', $itemBarber));

        $this->assertEquals(15, $itemBarber->fresh()->current_stock);
    }

    public function test_promo_approval_workflow_manager_to_owner(): void
    {
        // 1. Manager mengajukan promo cabang
        $this->actingAs($this->managerBarbershop)->post(route('promotions.store'), [
            'branch_id' => $this->branchBarbershop->id,
            'code' => 'DEMOPROMO15',
            'name' => 'Diskon Pembukaan 15%',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'quota' => 20,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(14)->toDateString(),
        ])->assertRedirect(route('promotions.index'));

        $promo = Promotion::where('code', 'DEMOPROMO15')->first();
        $this->assertNotNull($promo);
        $this->assertEquals(PromotionStatus::Pending, $promo->status);

        // 2. Checkout / validate promo menolak promo yang masih Pending
        $validateRes = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'DEMOPROMO15',
            'amount' => 100000,
            'branch_id' => $this->branchBarbershop->id,
        ]);
        $validateRes->assertStatus(422);
        $this->assertFalse($validateRes->json('valid'));

        // 3. Manager tidak bisa approve sendiri
        $this->actingAs($this->managerBarbershop)->post(route('promotions.approve', $promo))
            ->assertStatus(403);

        // 4. Owner menyetujui promo
        $this->actingAs($this->owner)->post(route('promotions.approve', $promo))
            ->assertSessionHas('success');

        $this->assertEquals(PromotionStatus::Approved, $promo->fresh()->status);

        // 5. Checkout sekarang menerima promo yang disetujui
        $validateRes2 = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'DEMOPROMO15',
            'amount' => 100000,
            'branch_id' => $this->branchBarbershop->id,
        ]);
        $validateRes2->assertStatus(200);
        $this->assertTrue($validateRes2->json('valid'));
        $this->assertEquals(15000, $validateRes2->json('discount'));
    }

    public function test_login_redirect_and_aliases_for_all_three_managers(): void
    {
        $this->post(route('login'), [
            'email' => 'manager.barbershop@simaul.com',
            'password' => 'password',
        ])->assertRedirect(route('manager.dashboard'));

        $this->post(route('login'), [
            'email' => 'manager.laundry@simaul.com',
            'password' => 'password',
        ])->assertRedirect(route('manager.dashboard'));

        $this->post(route('login'), [
            'email' => 'manager.autocare@simaul.com',
            'password' => 'password',
        ])->assertRedirect(route('manager.dashboard'));
    }
}
