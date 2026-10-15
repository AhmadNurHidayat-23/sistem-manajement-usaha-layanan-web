<?php

namespace Tests\Feature\Member;

use App\Enums\MembershipStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\LoyaltyPoint;
use App\Models\Membership;
use App\Models\Role;
use App\Models\User;
use App\Services\MembershipPromo\MemberService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrasiMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner System',
            'email' => 'owner@pos.test',
            'role_id' => $ownerRole->id,
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

    public function test_owner_and_cashier_can_view_members_index(): void
    {
        $response = $this->actingAs($this->owner)->get(route('members.index'));
        $response->assertOk();
        $response->assertViewIs('kasir.member.index');

        $responseCashier = $this->actingAs($this->cashier)->get(route('members.index'));
        $responseCashier->assertOk();
    }

    public function test_cashier_can_register_customer_as_member_with_welcome_points(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('members.store'), [
            'user_id' => $this->customer->id,
            'member_code' => 'MBR-TEST01',
        ]);

        $membership = Membership::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($membership);
        $this->assertEquals('MBR-TEST01', $membership->member_code);
        $this->assertEquals(MembershipStatus::Active, $membership->status);

        $loyaltyPoint = LoyaltyPoint::where('membership_id', $membership->id)->first();
        $this->assertNotNull($loyaltyPoint);
        $this->assertEquals(10, $loyaltyPoint->total_points);

        $this->assertDatabaseHas('loyalty_transactions', [
            'membership_id' => $membership->id,
            'points' => 10,
        ]);

        $response->assertRedirect(route('members.show', $membership));
    }

    public function test_customer_can_self_register_as_member(): void
    {
        $response = $this->actingAs($this->customer)->post(route('members.register-self'));
        $response->assertRedirect(route('members.my-membership'));

        $this->customer->refresh();
        $this->assertNotNull($this->customer->membership);
        $this->assertEquals(10, $this->customer->membership->loyaltyPoint->total_points);
    }

    public function test_customer_can_view_own_membership_and_points(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $member = $memberService->registerSelfAsMember($this->customer);
        $this->customer->refresh();

        $response = $this->actingAs($this->customer)->get(route('members.my-membership'));
        $response->assertOk();
        $response->assertViewIs('pelanggan.member.my-membership');
        $response->assertSee($member->member_code);
        $response->assertSee('Saldo Poin Saat Ini');
    }

    public function test_customer_can_view_packages(): void
    {
        $response = $this->actingAs($this->customer)->get(route('members.packages'));
        $response->assertOk();
        $response->assertViewIs('pelanggan.member.packages');
        $response->assertSee('Tingkat Keanggotaan');
    }
}
