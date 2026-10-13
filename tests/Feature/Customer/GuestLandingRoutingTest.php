<?php

namespace Tests\Feature\Customer;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestLandingRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Pratama',
            'email' => 'budi.pratama@example.com',
        ]);

        $this->branch = Branch::create([
            'name' => 'Laundry Express Sudirman',
            'address' => 'Jl. Sudirman No. 12',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Kiloan']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Kilat Express',
            'description' => 'Cuci higienis selesai dalam 3 jam.',
            'price' => 25000,
            'service_type' => 'dropoff',
            'estimated_duration' => 180,
            'is_active' => true,
        ]);

        Promotion::create([
            'code' => 'HEMAT20',
            'name' => 'Promo Diskon 20%',
            'description' => 'Potongan 20% untuk semua layanan.',
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
            'status' => PromotionStatus::Active,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Completed,
            'order_number' => 'ORD-REV-001',
            'total_amount' => 25000,
        ]);

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->customer->id,
            'order_id' => $order->id,
            'transaction_code' => 'TRX-REV-001',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        Ulasan::create([
            'user_id' => $this->customer->id,
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Sangat bersih dan rapi!',
        ]);
    }

    public function test_guest_can_access_root_url_without_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('landing.index');
    }

    public function test_guest_can_see_all_required_landing_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SIMAUL');
        $response->assertSee('BAGAIMANA JIKA');
        $response->assertSee('SEMUA LAYANAN');
        $response->assertSee('Katalog Layanan');
        $response->assertSee('Cuci Kilat Express');
        $response->assertSee('Promo & Voucher Aktif', false);
        $response->assertSee('HEMAT20');
        $response->assertSee('Loyalty Program');
        $response->assertSee('Kumpulkan Poin, Dapatkan Layanan Gratis!');
        $response->assertSee('Apa Kata Mereka?');
        $response->assertSee('Sangat bersih dan rapi!');
        $response->assertSee(route('login'));
        $response->assertSee('Login');
    }

    public function test_authenticated_customer_sees_same_landing_page_with_name_and_logout(): void
    {
        $response = $this->actingAs($this->customer)->get('/');

        $response->assertStatus(200);
        $response->assertSee('BAGAIMANA JIKA');
        $response->assertSee('SEMUA LAYANAN');
        $response->assertSee('Cuci Kilat Express');
        $response->assertSee('Budi Pratama');
        $response->assertSee('Logout');
        $response->assertSee(route('logout'));
    }

    public function test_customer_prefix_redirects_to_root(): void
    {
        $response = $this->get('/customer');

        $response->assertRedirect('/');
    }
}
