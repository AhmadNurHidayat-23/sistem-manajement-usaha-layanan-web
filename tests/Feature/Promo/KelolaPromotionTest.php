<?php

namespace Tests\Feature\Promo;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Timur',
            'address' => 'Jl. Pemuda No. 2',
            'phone' => '0822222222',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Store',
            'email' => 'owner@promo.test',
            'role_id' => $ownerRole->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Pusat',
            'email' => 'manager@promo.test',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Promo',
            'email' => 'customer@promo.test',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_owner_can_view_promotions_index(): void
    {
        $response = $this->actingAs($this->owner)->get(route('promotions.index'));
        $response->assertOk();
        $response->assertViewIs('owner.promo.index');
    }

    public function test_manager_can_view_promotions_index(): void
    {
        $response = $this->actingAs($this->manager)->get(route('promotions.index'));
        $response->assertOk();
    }

    public function test_owner_can_create_percentage_promotion(): void
    {
        $response = $this->actingAs($this->owner)->post(route('promotions.store'), [
            'code' => 'DISKON20',
            'name' => 'Diskon Awal Pekan 20%',
            'discount_type' => DiscountType::Percentage->value,
            'discount_value' => 20,
            'quota' => 50,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => PromotionStatus::Active->value,
        ]);

        $response->assertRedirect(route('promotions.index'));
        $this->assertDatabaseHas('promotions', [
            'code' => 'DISKON20',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'quota' => 50,
        ]);
    }

    public function test_manager_cannot_create_promo_for_other_branch(): void
    {
        $response = $this->actingAs($this->manager)->post(route('promotions.store'), [
            'branch_id' => $this->branch2->id,
            'code' => 'ILEGALPROMO',
            'name' => 'Promo Tak Berizin',
            'discount_type' => DiscountType::Fixed->value,
            'discount_value' => 15000,
            'quota' => 10,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_update_promo(): void
    {
        $promo = Promotion::create([
            'code' => 'SUPER50',
            'name' => 'Super Deal',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 50000,
            'quota' => 100,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->put(route('promotions.update', $promo), [
            'code' => 'SUPER50',
            'name' => 'Super Deal Update 60K',
            'discount_type' => DiscountType::Fixed->value,
            'discount_value' => 60000,
            'quota' => 120,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => PromotionStatus::Active->value,
        ]);

        $response->assertRedirect(route('promotions.index'));
        $this->assertDatabaseHas('promotions', [
            'id' => $promo->id,
            'name' => 'Super Deal Update 60K',
            'discount_value' => 60000,
            'quota' => 120,
        ]);
    }

    public function test_owner_can_toggle_promo_status(): void
    {
        $promo = Promotion::create([
            'code' => 'TOGGLEME',
            'name' => 'Promo Toggle Test',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->post(route('promotions.toggle-status', $promo));
        $response->assertRedirect();
        $this->assertEquals(PromotionStatus::Inactive, $promo->fresh()->status);

        $this->actingAs($this->owner)->post(route('promotions.toggle-status', $promo));
        $this->assertEquals(PromotionStatus::Active, $promo->fresh()->status);
    }

    public function test_customer_can_view_public_promotions(): void
    {
        Promotion::create([
            'code' => 'PUBLIC10',
            'name' => 'Kupon Publik 10%',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->customer)->get(route('promotions.public'));
        $response->assertOk();
        $response->assertViewIs('pelanggan.promo.public');
        $response->assertSee('PUBLIC10');
    }
}
