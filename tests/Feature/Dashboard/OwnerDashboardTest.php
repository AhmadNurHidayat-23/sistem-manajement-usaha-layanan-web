<?php

namespace Tests\Feature\Dashboard;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman Pusat',
            'address' => 'Jl. Jenderal Sudirman No. 10',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Thamrin Barat',
            'address' => 'Jl. MH Thamrin No. 20',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Utama',
            'role_id' => $ownerRole->id,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Siti',
            'role_id' => $cashierRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Budi Customer',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_owner_dashboard_displays_real_database_metrics(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Hair Care',
            'branch_id' => $this->branch1->id,
        ]);
        $service = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Haircut Premium',
            'price' => 150000,
            'estimated_duration' => 45,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Andi Stylist',
            'phone' => '0899999999',
            'status' => 'active',
            'joined_at' => Carbon::now(),
        ]);

        $booking = Booking::create([
            'branch_id' => $this->branch1->id,
            'user_id' => $this->customer->id,
            'service_id' => $service->id,
            'employee_id' => $employee->id,
            'booking_code' => 'BK-101',
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00:00',
            'status' => 'completed',
            'notes' => 'Customer VIP',
        ]);

        $trx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-101',
            'subtotal' => 150000,
            'discount' => 15000,
            'tax' => 0,
            'total' => 135000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'service_id' => $service->id,
            'price' => 150000,
            'qty' => 1,
            'subtotal' => 150000,
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'payment_method' => 'cash',
            'amount' => 135000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::now(),
        ]);

        $promo = Promotion::create([
            'branch_id' => $this->branch1->id,
            'code' => 'DISC10',
            'name' => 'Diskon Pembukaan',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 15000,
            'quota' => 100,
            'start_date' => Carbon::today()->subDays(1),
            'end_date' => Carbon::today()->addDays(30),
            'status' => PromotionStatus::Active,
        ]);

        PromotionUsage::create([
            'promotion_id' => $promo->id,
            'user_id' => $this->customer->id,
            'transaction_id' => $trx->id,
            'used_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Owner');
        $response->assertSee('Cabang Sudirman Pusat');
        $response->assertSee('Cabang Thamrin Barat');
        $response->assertSee('Haircut Premium');
        $response->assertSee('Andi Stylist');
        $response->assertSee('DISC10');
        $response->assertSee('TRX-101');
        $response->assertSee('135.000');
        $response->assertDontSee('Status Sistem');
    }

    public function test_unauthorized_roles_cannot_access_owner_dashboard(): void
    {
        $this->actingAs($this->cashier)->get(route('owner.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('owner.dashboard'))->assertStatus(403);
    }
}
