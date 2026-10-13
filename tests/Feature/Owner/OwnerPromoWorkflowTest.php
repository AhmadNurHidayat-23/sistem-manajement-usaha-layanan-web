<?php

namespace Tests\Feature\Owner;

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

class OwnerPromoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $customer;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Sudirman No. 10',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner SIMAUL',
            'email' => 'owner.test@simaul.com',
            'role_id' => $ownerRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer Budi',
            'email' => 'customer.test@simaul.com',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_pending_and_rejected_promos_do_not_appear_in_public_catalog(): void
    {
        $activePromo = Promotion::create([
            'code' => 'ACTIVEPROMO',
            'name' => 'Promo Diskon Aktif',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Active,
        ]);

        $pendingPromo = Promotion::create([
            'code' => 'BARBERNEW',
            'name' => 'Promo Barber Pending',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Pending,
        ]);

        $rejectedPromo = Promotion::create([
            'code' => 'REJECTEDPROMO',
            'name' => 'Promo Ditolak',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Rejected,
        ]);

        // Customer view
        $responseCustomer = $this->actingAs($this->customer)->get(route('promotions.public'));
        $responseCustomer->assertOk();
        $responseCustomer->assertSee('ACTIVEPROMO');
        $responseCustomer->assertDontSee('BARBERNEW');
        $responseCustomer->assertDontSee('REJECTEDPROMO');

        // Owner view on public catalog
        $responseOwner = $this->actingAs($this->owner)->get(route('promotions.public'));
        $responseOwner->assertOk();
        $responseOwner->assertSee('ACTIVEPROMO');
        $responseOwner->assertDontSee('BARBERNEW');
        $responseOwner->assertDontSee('REJECTEDPROMO');
        // Must not contain internal staff sidebar or personal customer actions for owner
        $responseOwner->assertDontSee('Poin Loyalitas Saya');
        $responseOwner->assertDontSee('Gunakan Saat Booking');
    }

    public function test_pending_promo_cannot_be_validated(): void
    {
        Promotion::create([
            'code' => 'PENDINGVALID',
            'name' => 'Promo Pending Validasi',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 10000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'status' => PromotionStatus::Pending,
        ]);

        $response = $this->actingAs($this->owner)->postJson(route('promotions.validate'), [
            'code' => 'PENDINGVALID',
            'amount' => 50000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'valid' => false,
            'message' => 'Kode promo ini sedang tidak aktif.',
        ]);
    }

    public function test_owner_can_approve_pending_promo(): void
    {
        $promo = Promotion::create([
            'code' => 'APPROVEME',
            'name' => 'Promo Diajukan Cabang',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 15,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Pending,
        ]);

        $response = $this->actingAs($this->owner)->post(route('promotions.approve', $promo));
        $response->assertRedirect();
        $this->assertEquals(PromotionStatus::Approved, $promo->fresh()->status);
    }

    public function test_owner_can_reject_pending_promo(): void
    {
        $promo = Promotion::create([
            'code' => 'REJECTME',
            'name' => 'Promo Tidak Sesuai',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 5000,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Pending,
        ]);

        $response = $this->actingAs($this->owner)->post(route('promotions.reject', $promo));
        $response->assertRedirect();
        $this->assertEquals(PromotionStatus::Rejected, $promo->fresh()->status);
    }

    public function test_owner_promotions_index_contains_modal_triggers_and_clean_actions(): void
    {
        $promoPending = Promotion::create([
            'code' => 'PENDINGROW',
            'name' => 'Row Pending',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Pending,
        ]);

        $promoActive = Promotion::create([
            'code' => 'ACTIVEROW',
            'name' => 'Row Aktif',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'status' => PromotionStatus::Active,
        ]);

        $response = $this->actingAs($this->owner)->get(route('promotions.index'));
        $response->assertOk();
        $response->assertSee('modal-create-promo');
        $response->assertSee('modal-edit-promo');
        $response->assertSee('modal-action-approve');
        $response->assertSee('modal-action-reject');
        $response->assertSee('modal-action-toggle');
        $response->assertSee('Setujui');
        $response->assertSee('Tolak');
        $response->assertSee('Nonaktifkan');
        $response->assertSee('Edit');
    }

    public function test_karyawan_index_does_not_contain_duplicate_navigation_buttons(): void
    {
        $response = $this->actingAs($this->owner)->get(route('employees.index'));
        $response->assertOk();
        $response->assertSee('Tambah Karyawan');
        // Header duplicate buttons should not exist
        $response->assertDontSee('Jadwal Shift');
        $response->assertDontSee('Rekap Komisi');
    }
}
