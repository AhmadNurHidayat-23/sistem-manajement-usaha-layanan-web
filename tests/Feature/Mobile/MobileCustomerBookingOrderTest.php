<?php

namespace Tests\Feature\Mobile;

use App\Enums\BookingStatus;
use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCustomerBookingOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_customer_can_create_and_view_booking(): void
    {
        $branchA = Branch::create(['name' => 'Cabang A', 'address' => 'Jl. A', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Styling']);
        $serviceA = Service::create([
            'branch_id' => $branchA->id,
            'service_category_id' => $category->id,
            'name' => 'Potong Rambut',
            'price' => 80000,
            'service_type' => 'appointment',
            'estimated_duration' => 45,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create(['role_id' => $customerRole?->id]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $login->json('data.token');

        // Create booking
        $bookingResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/bookings', [
                'branch_id' => $branchA->id,
                'service_id' => $serviceA->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '10:00',
                'notes' => 'Catatan pelanggan',
            ]);

        $bookingResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.service.duration_formatted', '±1 jam');

        $bookingId = $bookingResponse->json('data.id');

        // View detail booking
        $detailResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/mobile/customer/bookings/{$bookingId}");

        $detailResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $bookingId)
            ->assertJsonPath('data.service.duration_formatted', '±1 jam')
            ->assertJsonPath('data.notes', 'Catatan pelanggan');
    }

    public function test_customer_cannot_select_service_from_different_branch(): void
    {
        $branchA = Branch::create(['name' => 'Cabang A', 'address' => 'Jl. A', 'is_active' => true]);
        $branchB = Branch::create(['name' => 'Cabang B', 'address' => 'Jl. B', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Kategori']);

        // Service belongs to Branch B
        $serviceB = Service::create([
            'branch_id' => $branchB->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan B',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create(['role_id' => $customerRole?->id]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $login->json('data.token');

        // Trying to book serviceB with branchA -> MUST BE REJECTED (422)
        $invalidBooking = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/bookings', [
                'branch_id' => $branchA->id,
                'service_id' => $serviceB->id,
                'booking_date' => now()->addDay()->toDateString(),
                'booking_time' => '11:00',
            ]);

        $invalidBooking->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Layanan yang dipilih tidak tersedia pada cabang tersebut.');

        // Trying to order serviceB with branchA -> MUST BE REJECTED (422)
        $invalidOrder = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/orders', [
                'branch_id' => $branchA->id,
                'service_id' => $serviceB->id,
            ]);

        $invalidOrder->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Layanan yang dipilih tidak tersedia pada cabang tersebut.');
    }

    public function test_customer_can_create_order_without_weight(): void
    {
        $branch = Branch::create(['name' => 'Cabang Laundry', 'address' => 'Jl. Laundry 1', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Laundry']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Kering Kiloan',
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create(['role_id' => $customerRole?->id]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $token = $login->json('data.token');

        // Customer does NOT submit estimated_quantity (weight will be weighed by employee upon arrival)
        $orderResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/orders', [
                'branch_id' => $branch->id,
                'service_id' => $service->id,
                'pickup_option' => false,
                'notes' => 'Tolong pisahkan pakaian putih',
            ]);

        $orderResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.service.is_weight_based', true)
            ->assertJsonPath('data.service.duration_formatted', '±2 jam')
            ->assertJsonPath('data.estimated_quantity', null);

        $orderId = $orderResponse->json('data.id');

        // View detail order
        $detailResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/mobile/customer/orders/{$orderId}");

        $detailResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.service.duration_formatted', '±2 jam')
            ->assertJsonPath('data.notes', 'Tolong pisahkan pakaian putih');
    }

    public function test_customer_can_view_promos_and_membership(): void
    {
        $branch = Branch::create(['name' => 'Cabang Promo', 'address' => 'Jl. Promo', 'is_active' => true]);
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $user = User::factory()->create(['role_id' => $customerRole?->id]);

        // Active promo
        Promotion::create([
            'branch_id' => $branch->id,
            'code' => 'DISKON10',
            'name' => 'Diskon 10 Persen',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'quota' => 50,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        // Expired promo (should NOT be returned)
        Promotion::create([
            'branch_id' => $branch->id,
            'code' => 'EXPIRED99',
            'name' => 'Diskon Expired',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 5000,
            'quota' => 50,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        $login = $this->postJson('/api/mobile/login', ['email' => $user->email, 'password' => 'password']);
        $token = $login->json('data.token');

        // Test promos endpoint
        $promoRes = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/mobile/customer/promos');
        $promoRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'DISKON10');

        // Test membership before registering
        $memRes = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/mobile/customer/membership');
        $memRes->assertStatus(200)
            ->assertJsonPath('data', null);

        // Self-register membership
        $regRes = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/mobile/customer/membership/register');
        $regRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.points', 0);

        // View membership after register
        $memAfterRes = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/mobile/customer/membership');
        $memAfterRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active');
    }

    public function test_customer_history_and_detail_isolation(): void
    {
        $branch = Branch::create(['name' => 'Cabang Isolasi', 'address' => 'Jl. Isolasi', 'is_active' => true]);
        $category = ServiceCategory::create(['name' => 'Umum']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Layanan Isolasi',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $userA = User::factory()->create(['role_id' => $customerRole?->id]);
        $userB = User::factory()->create(['role_id' => $customerRole?->id]);

        $bookingA = Booking::create([
            'user_id' => $userA->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $orderA = Order::create([
            'user_id' => $userA->id,
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'estimated_quantity' => 2,
            'status' => StatusPesanan::Pending,
        ]);

        // User B logs in
        $loginB = $this->postJson('/api/mobile/login', ['email' => $userB->email, 'password' => 'password']);
        $tokenB = $loginB->json('data.token');

        // User B cannot see User A's bookings or orders
        $bBookings = $this->withHeader('Authorization', 'Bearer '.$tokenB)->getJson('/api/mobile/customer/bookings');
        $bBookings->assertStatus(200)->assertJsonCount(0, 'data');

        $bOrders = $this->withHeader('Authorization', 'Bearer '.$tokenB)->getJson('/api/mobile/customer/orders');
        $bOrders->assertStatus(200)->assertJsonCount(0, 'data');

        // User B detail attempt on User A's booking returns 404
        $bDetailBooking = $this->withHeader('Authorization', 'Bearer '.$tokenB)->getJson("/api/mobile/customer/bookings/{$bookingA->id}");
        $bDetailBooking->assertStatus(404);

        // User B detail attempt on User A's order returns 404
        $bDetailOrder = $this->withHeader('Authorization', 'Bearer '.$tokenB)->getJson("/api/mobile/customer/orders/{$orderA->id}");
        $bDetailOrder->assertStatus(404);
    }
}
