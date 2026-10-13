<?php

namespace Tests\Feature\Member;

use App\Enums\BookingStatus;
use App\Enums\CashierShiftStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Modules\KasirPOS\Services\TransaksiService;
use App\Modules\MembershipPromo\Services\MemberService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoinLoyalitasMemberTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir 1',
            'email' => 'kasir@pos.test',
            'role_id' => $cashierRole->id,
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Setia',
            'email' => 'customer@pos.test',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_member_points_increase_automatically_from_pos_transaction(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $membership = $memberService->registerSelfAsMember($this->customer);

        $this->assertEquals(10, $membership->loyaltyPoint->fresh()->total_points);

        $category = ServiceCategory::create(['name' => 'Perawatan', 'branch_id' => $this->branch->id]);
        $service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Full Detailing',
            'price' => 100000,
            'estimated_duration' => 60,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        CashierShift::create([
            'user_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500000,
            'status' => CashierShiftStatus::Open,
        ]);

        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        /** @var TransaksiService $posService */
        $posService = app(TransaksiService::class);
        $posService->createTransaction($this->cashier, [
            'branch_id' => $this->branch->id,
            'booking_id' => $booking->id,
            'items' => [
                ['service_id' => $service->id, 'qty' => 1],
            ],
            'payment_method' => 'cash',
            'cash_received' => 100000,
        ]);

        $freshPoints = $membership->loyaltyPoint->fresh()->total_points;
        $this->assertEquals(20, $freshPoints);

        $this->assertDatabaseHas('loyalty_transactions', [
            'membership_id' => $membership->id,
            'points' => 10,
        ]);
    }

    public function test_customer_can_redeem_points(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $membership = $memberService->registerSelfAsMember($this->customer);

        $membership->loyaltyPoint->increment('total_points', 100);
        $this->assertEquals(110, $membership->loyaltyPoint->fresh()->total_points);

        $response = $this->actingAs($this->customer)->post(route('members.redeem', $membership), [
            'points' => 50,
            'reward_description' => 'Voucher Diskon Rp 10.000',
        ]);

        $response->assertRedirect();
        $this->assertEquals(60, $membership->loyaltyPoint->fresh()->total_points);

        $this->assertDatabaseHas('loyalty_transactions', [
            'membership_id' => $membership->id,
            'points' => -50,
            'description' => 'Voucher Diskon Rp 10.000',
        ]);
    }

    public function test_redeeming_more_points_than_balance_fails_validation(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $membership = $memberService->registerSelfAsMember($this->customer);

        $response = $this->actingAs($this->customer)->post(route('members.redeem', $membership), [
            'points' => 100,
            'reward_description' => 'Voucher Diskon Rp 25.000',
        ]);

        $response->assertSessionHasErrors(['points']);
        $this->assertEquals(10, $membership->loyaltyPoint->fresh()->total_points);
    }
}
