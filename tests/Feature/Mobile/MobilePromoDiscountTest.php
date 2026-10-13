<?php

namespace Tests\Feature\Mobile;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobilePromoDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_customer_booking_and_order_promo_validation_and_discounts(): void
    {
        $branch1 = Branch::create(['name' => 'Cabang Barat', 'address' => 'Jl. Barat', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Cabang Timur', 'address' => 'Jl. Timur', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Kategori Promo']);

        $service1 = Service::create([
            'branch_id' => $branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Premium',
            'price' => 100000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $service2 = Service::create([
            'branch_id' => $branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Karpet Kiloan',
            'price' => 20000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create([
            'role_id' => $customerRole?->id,
            'password' => Hash::make('password123'),
        ]);
        $loginRes = $this->postJson('/api/mobile/login', ['email' => $customer->email, 'password' => 'password123']);
        $token = $loginRes->json('data.token');

        // 1. Promo Valid untuk Cabang 1 (Diskon 20%)
        Promotion::create([
            'branch_id' => $branch1->id,
            'code' => 'DISCOUNT20',
            'name' => 'Diskon 20 Persen',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 20,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        // 2. Promo untuk Cabang 2
        Promotion::create([
            'branch_id' => $branch2->id,
            'code' => 'TIMUR10',
            'name' => 'Khusus Timur',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 10000,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        // 3. Promo Expired
        Promotion::create([
            'branch_id' => $branch1->id,
            'code' => 'EXPIRED99',
            'name' => 'Promo Kadaluarsa',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 50,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        // A. Booking TANPA promo berhasil
        $noPromoRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $branch1->id,
            'service_id' => $service1->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '10:00',
        ]);
        $noPromoRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.promo_code', null)
            ->assertJsonPath('data.subtotal', 100000)
            ->assertJsonPath('data.discount_amount', 0)
            ->assertJsonPath('data.total_amount', 100000);

        // B. Booking DENGAN promo valid berhasil dan diskon diterapkan (20% dari 100.000 = 20.000, total = 80.000)
        $withPromoRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $branch1->id,
            'service_id' => $service1->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '11:00',
            'promo_code' => 'DISCOUNT20',
        ]);
        $withPromoRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.promo_code', 'DISCOUNT20')
            ->assertJsonPath('data.subtotal', 100000)
            ->assertJsonPath('data.discount_amount', 20000)
            ->assertJsonPath('data.total_amount', 80000);

        // C. Booking dengan promo cabang lain ditolak 422
        $wrongBranchRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $branch1->id,
            'service_id' => $service1->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '12:00',
            'promo_code' => 'TIMUR10',
        ]);
        $wrongBranchRes->assertStatus(422)
            ->assertJsonPath('success', false);

        // D. Booking dengan promo expired ditolak 422
        $expiredRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $branch1->id,
            'service_id' => $service1->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '13:00',
            'promo_code' => 'EXPIRED99',
        ]);
        $expiredRes->assertStatus(422)
            ->assertJsonPath('success', false);

        // E. Booking dengan kode promo ngawur ditolak 422
        $invalidRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $branch1->id,
            'service_id' => $service1->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '14:00',
            'promo_code' => 'KODENGAWUR',
        ]);
        $invalidRes->assertStatus(422)
            ->assertJsonPath('success', false);

        // F. Order Drop-off dengan promo valid (3 kg x 20.000 = 60.000, diskon 20% = 12.000, total = 48.000)
        $orderRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/orders', [
            'branch_id' => $branch1->id,
            'service_id' => $service2->id,
            'estimated_quantity' => 3,
            'promo_code' => 'DISCOUNT20',
        ]);
        $orderRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.promo_code', 'DISCOUNT20')
            ->assertJsonPath('data.subtotal', 60000)
            ->assertJsonPath('data.discount_amount', 12000)
            ->assertJsonPath('data.total_amount', 48000);
    }
}
