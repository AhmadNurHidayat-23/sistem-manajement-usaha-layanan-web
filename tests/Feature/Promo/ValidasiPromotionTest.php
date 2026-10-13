<?php

namespace Tests\Feature\Promo;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
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

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Promo',
            'email' => 'customer@promo.test',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_validate_promo_api_success_percentage(): void
    {
        Promotion::create([
            'code' => 'HEMAT25',
            'name' => 'Hemat 25 Persen',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 25,
            'quota' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'HEMAT25',
            'amount' => 200000,
            'branch_id' => $this->branch1->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount' => 50000,
            'final_total' => 150000,
        ]);
    }

    public function test_validate_promo_api_success_fixed(): void
    {
        Promotion::create([
            'code' => 'POTONG30K',
            'name' => 'Potongan 30 Ribu',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 30000,
            'quota' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'POTONG30K',
            'amount' => 100000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount' => 30000,
            'final_total' => 70000,
        ]);
    }

    public function test_validate_promo_api_rejects_expired(): void
    {
        Promotion::create([
            'code' => 'EXPIRED99',
            'name' => 'Promo Telah Habis',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 50,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(2),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'EXPIRED99',
            'amount' => 100000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_validate_promo_api_rejects_branch_mismatch(): void
    {
        Promotion::create([
            'branch_id' => $this->branch1->id,
            'code' => 'PUSATONLY',
            'name' => 'Khusus Cabang Pusat',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 20000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'PUSATONLY',
            'amount' => 100000,
            'branch_id' => $this->branch2->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_validate_promo_api_rejects_quota_exhausted(): void
    {
        $promo = Promotion::create([
            'code' => 'KUOTA1',
            'name' => 'Kupon Kuota 1',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 10000,
            'quota' => 1,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'status' => PromotionStatus::Active,
        ]);

        PromotionUsage::create([
            'promotion_id' => $promo->id,
            'user_id' => $this->customer->id,
            'used_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'KUOTA1',
            'amount' => 50000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
        ]);
    }
}
