<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderDetailUiFinalTest extends TestCase
{
    use RefreshDatabase;

    protected User $andi;

    protected User $budi;

    protected User $charlie;

    protected Branch $barbershop;

    protected Branch $laundry;

    protected Branch $autoCare;

    protected Service $laundryService;

    protected Order $andiOrder;

    protected Order $budiOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->andi = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Andi Pratama',
            'email' => 'andi@simaul.com',
        ]);

        $this->budi = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@simaul.com',
        ]);

        $this->charlie = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Charlie Ang',
            'email' => 'charlie@example.com',
        ]);

        $cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir Demo',
            'email' => 'kasir@simaul.com',
        ]);

        $this->barbershop = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Sudirman No. 10',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        $this->laundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. Fatmawati No. 25',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $this->autoCare = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Panjang No. 88',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Laundry Kiloan']);
        $this->laundryService = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->laundry->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 10000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 1440,
            'is_active' => true,
        ]);

        $this->andiOrder = Order::create([
            'user_id' => $this->andi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Processing,
            'estimated_quantity' => 3.5,
        ]);

        $this->budiOrder = Order::create([
            'user_id' => $this->budi->id,
            'branch_id' => $this->laundry->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Completed,
            'estimated_quantity' => 5.0,
        ]);

        Transaction::create([
            'order_id' => $this->budiOrder->id,
            'branch_id' => $this->laundry->id,
            'cashier_id' => $cashier->id,
            'transaction_code' => 'TRX-BUDI-166',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
    }

    public function test_customer_selects_order_via_post_and_redirects_to_static_detail_url(): void
    {
        $response = $this->actingAs($this->andi)
            ->post(route('customer.pesanan.select'), [
                'order_id' => $this->andiOrder->id,
            ]);

        $response->assertRedirect(route('customer.pesanan.detail'));
        $this->assertEquals($this->andiOrder->id, session('selected_order_id'));
    }

    public function test_order_detail_view_has_clean_url_breadcrumb_heading_and_title(): void
    {
        $response = $this->actingAs($this->andi)
            ->withSession(['selected_order_id' => $this->andiOrder->id])
            ->get(route('customer.pesanan.detail'));

        $response->assertStatus(200);
        $response->assertSee('<title>Detail Pesanan</title>', false);
        $response->assertSee('Detail Pesanan');
        $response->assertSee('Pesanan Saya');
        $response->assertDontSee('Detail Pesanan #'.$this->andiOrder->id);
        $response->assertDontSee('Order #'.$this->andiOrder->id);
    }

    public function test_title_is_detail_pesanan_for_both_andi_and_budi_without_dynamic_ids(): void
    {
        $andiResponse = $this->actingAs($this->andi)
            ->withSession(['selected_order_id' => $this->andiOrder->id])
            ->get(route('customer.pesanan.detail'));
        $andiResponse->assertSee('<title>Detail Pesanan</title>', false);
        $andiResponse->assertDontSee('<title>Detail Pesanan #'.$this->andiOrder->id.'</title>', false);
        $andiResponse->assertDontSee('Order #'.$this->andiOrder->id);

        $budiResponse = $this->actingAs($this->budi)
            ->withSession(['selected_order_id' => $this->budiOrder->id])
            ->get(route('customer.pesanan.detail'));
        $budiResponse->assertSee('<title>Detail Pesanan</title>', false);
        $budiResponse->assertDontSee('<title>Detail Pesanan #'.$this->budiOrder->id.'</title>', false);
        $budiResponse->assertDontSee('Order #'.$this->budiOrder->id);
    }

    public function test_other_pages_retain_their_own_distinct_titles(): void
    {
        $ordersResponse = $this->actingAs($this->andi)->get(route('customer.pesanan.index'));
        $ordersResponse->assertSee('<title>Pesanan Saya</title>', false);

        $dashResponse = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $dashResponse->assertSee('<title>Dashboard Pelanggan</title>', false);
    }

    public function test_direct_access_without_session_redirects_safely_to_orders_list(): void
    {
        $response = $this->actingAs($this->andi)->get(route('customer.pesanan.detail'));
        $response->assertRedirect(route('customer.pesanan.index'));
        $response->assertSessionHas('info');
    }

    public function test_customer_cannot_select_or_view_other_customers_order(): void
    {
        $selectResponse = $this->actingAs($this->andi)->post(route('customer.pesanan.select'), [
            'order_id' => $this->budiOrder->id,
        ]);
        $selectResponse->assertStatus(403);

        $detailResponse = $this->actingAs($this->andi)
            ->withSession(['selected_order_id' => $this->budiOrder->id])
            ->get(route('customer.pesanan.detail'));
        $detailResponse->assertStatus(403);
    }

    public function test_refresh_maintains_order_detail_state(): void
    {
        $first = $this->actingAs($this->andi)
            ->withSession(['selected_order_id' => $this->andiOrder->id])
            ->get(route('customer.pesanan.detail'));
        $first->assertStatus(200);

        $refresh = $this->actingAs($this->andi)
            ->withSession(['selected_order_id' => $this->andiOrder->id])
            ->get(route('customer.pesanan.detail'));
        $refresh->assertStatus(200);
        $refresh->assertSee('<title>Detail Pesanan</title>', false);
    }

    public function test_review_permission_rules_andi_processing_vs_budi_completed(): void
    {
        $andiReview = $this->actingAs($this->andi)->get(route('customer.ulasan.create', [
            'order_id' => $this->andiOrder->id,
        ]));
        $andiReview->assertSessionHas('error');

        $budiReview = $this->actingAs($this->budi)->get(route('customer.ulasan.create', [
            'order_id' => $this->budiOrder->id,
        ]));
        $budiReview->assertStatus(200);
        $budiReview->assertSee('<title>Beri Ulasan</title>', false);
    }

    public function test_clean_branch_display_names_and_navy_action_buttons(): void
    {
        $this->assertEquals('Barbershop & Studio', $this->barbershop->display_name);
        $this->assertEquals('Laundry & Shoe Care', $this->laundry->display_name);
        $this->assertEquals('Auto Care & Detailing', $this->autoCare->display_name);

        $dashResponse = $this->actingAs($this->andi)->get(route('customer.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Barbershop & Studio');
        $dashResponse->assertSee('Laundry & Shoe Care');
        $dashResponse->assertSee('Auto Care & Detailing');
        $dashResponse->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $dashResponse->assertDontSee('<script>', false);
    }
}
