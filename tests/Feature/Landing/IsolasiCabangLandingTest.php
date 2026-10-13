<?php

namespace Tests\Feature\Landing;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolasiCabangLandingTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;

    protected Branch $branchB;

    protected Service $serviceA1;

    protected Service $serviceB1;

    protected Promotion $promoA1;

    protected Promotion $promoB1;

    protected Ulasan $ulasanA1;

    protected Ulasan $ulasanB1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customerA = User::factory()->create(['role_id' => $customerRole?->id, 'name' => 'Pelanggan A']);
        $customerB = User::factory()->create(['role_id' => $customerRole?->id, 'name' => 'Pelanggan B']);

        $this->branchA = Branch::create([
            'name' => 'Laundry Berkah Utama',
            'address' => 'Jl. Merdeka No. 1, Jakarta',
            'phone' => '021-111111',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'name' => 'Laundry Kilat Cepat',
            'address' => 'Jl. Sudirman No. 99, Surabaya',
            'phone' => '031-222222',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Cuci Reguler & Premium', 'is_active' => true]);

        $this->serviceA1 = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Layanan Cuci Premium A1',
            'description' => 'Khusus pengerjaan teliti di outlet Berkah Utama.',
            'price' => 150000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $this->serviceB1 = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Layanan Express Kilat B1',
            'description' => 'Khusus pengerjaan kilat di outlet Kilat Cepat.',
            'price' => 180000,
            'service_type' => 'drop_off',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);

        $this->promoA1 = Promotion::create([
            'branch_id' => $this->branchA->id,
            'code' => 'PROMO-BERKAH-A1',
            'name' => 'Voucher Diskon Berkah A1',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 25000,
            'quota' => 50,
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        $this->promoB1 = Promotion::create([
            'branch_id' => $this->branchB->id,
            'code' => 'PROMO-KILAT-B1',
            'name' => 'Voucher Diskon Kilat B1',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 20,
            'quota' => 50,
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => PromotionStatus::Active,
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $cashier = User::factory()->create(['role_id' => $cashierRole?->id, 'name' => 'Kasir Demo']);

        $trxA = Transaction::create([
            'transaction_code' => 'TRX-TEST-A1',
            'cashier_id' => $cashier->id,
            'branch_id' => $this->branchA->id,
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'transaction_date' => now(),
            'status' => StatusTransaksi::Paid,
        ]);

        $this->ulasanA1 = Ulasan::create([
            'user_id' => $customerA->id,
            'transaction_id' => $trxA->id,
            'service_id' => $this->serviceA1->id,
            'rating' => 5,
            'komentar' => 'Hasil cuci di Berkah A1 sangat wangi dan luar biasa rapi.',
        ]);

        $trxB = Transaction::create([
            'transaction_code' => 'TRX-TEST-B1',
            'cashier_id' => $cashier->id,
            'branch_id' => $this->branchB->id,
            'subtotal' => 180000,
            'discount' => 0,
            'total' => 180000,
            'transaction_date' => now(),
            'status' => StatusTransaksi::Paid,
        ]);

        $this->ulasanB1 = Ulasan::create([
            'user_id' => $customerB->id,
            'transaction_id' => $trxB->id,
            'service_id' => $this->serviceB1->id,
            'rating' => 5,
            'komentar' => 'Pengerjaan Kilat B1 super ekspres dan sangat cepat.',
        ]);
    }

    public function test_landing_usaha_a_shows_its_own_data_and_never_leaks_usaha_b_data(): void
    {
        $response = $this->get(route('customer.home', ['branch_id' => $this->branchA->id]));

        $response->assertStatus(200);
        $response->assertSee('Laundry Berkah Utama');
        $response->assertSee($this->serviceA1->name);
        $response->assertSee($this->promoA1->code);
        $response->assertSee($this->ulasanA1->komentar);

        $response->assertDontSee($this->serviceB1->name);
        $response->assertDontSee($this->promoB1->code);
        $response->assertDontSee($this->ulasanB1->komentar);
    }

    public function test_landing_usaha_b_shows_its_own_data_and_never_leaks_usaha_a_data(): void
    {
        $response = $this->get(route('customer.home', ['branch_id' => $this->branchB->id]));

        $response->assertStatus(200);
        $response->assertSee('Laundry Kilat Cepat');
        $response->assertSee($this->serviceB1->name);
        $response->assertSee($this->promoB1->code);
        $response->assertSee($this->ulasanB1->komentar);

        $response->assertDontSee($this->serviceA1->name);
        $response->assertDontSee($this->promoA1->code);
        $response->assertDontSee($this->ulasanA1->komentar);
    }

    public function test_featured_service_is_scoped_to_current_branch(): void
    {
        $responseA = $this->get(route('customer.home', ['branch_id' => $this->branchA->id]));
        $responseA->assertStatus(200);
        $responseA->assertSee($this->serviceA1->name);

        $responseB = $this->get(route('customer.home', ['branch_id' => $this->branchB->id]));
        $responseB->assertStatus(200);
        $responseB->assertSee($this->serviceB1->name);
    }
}
