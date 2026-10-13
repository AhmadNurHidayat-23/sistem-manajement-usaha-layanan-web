<?php

namespace Tests\Feature\Dashboard;

use App\Enums\StatusPembayaran;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $cashier;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
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

        $this->manager = User::factory()->create([
            'name' => 'Manager Sudirman',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Siti',
            'role_id' => $cashierRole->id,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Budi Customer',
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_manager_dashboard_displays_scoped_branch_data_and_header(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Body Treatment',
            'branch_id' => $this->branch1->id,
        ]);
        $service1 = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Massage Terapi',
            'price' => 200000,
            'estimated_duration' => 60,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Rina Terapis',
            'phone' => '0877777777',
            'status' => 'active',
            'joined_at' => Carbon::now(),
        ]);

        EmployeeShift::create([
            'branch_id' => $this->branch1->id,
            'employee_id' => $employee->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'status' => StatusShiftKaryawan::Present,
        ]);

        Booking::create([
            'branch_id' => $this->branch1->id,
            'user_id' => $this->customer->id,
            'service_id' => $service1->id,
            'employee_id' => $employee->id,
            'booking_code' => 'BK-MGR-01',
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '11:00:00',
            'status' => 'confirmed',
        ]);

        $trxBranch1 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'transaction_code' => 'TRX-B1-001',
            'subtotal' => 200000,
            'discount' => 0,
            'tax' => 0,
            'total' => 200000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        Payment::create([
            'transaction_id' => $trxBranch1->id,
            'payment_method' => 'qris',
            'amount' => 200000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => Carbon::now(),
        ]);

        Transaction::create([
            'branch_id' => $this->branch2->id,
            'cashier_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'transaction_code' => 'TRX-B2-SECRET',
            'subtotal' => 500000,
            'discount' => 0,
            'tax' => 0,
            'total' => 500000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->branch1->display_name);
        $response->assertSee('Jl. Jenderal Sudirman No. 10');
        $response->assertSee('TRX-B1-001');
        $response->assertSee('Rina Terapis');
        $response->assertSee('Massage Terapi');
        $response->assertSee('200.000');
        $response->assertDontSee('TRX-B2-SECRET');
    }

    public function test_manager_without_assigned_branch_displays_notice_gracefully(): void
    {
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $unassignedManager = User::factory()->create([
            'name' => 'Manager Tanpa Cabang',
            'role_id' => $managerRole->id,
        ]);

        $response = $this->actingAs($unassignedManager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Cabang Belum Ditugaskan');
        $response->assertSee('Akun Anda belum terdaftar mengelola cabang manapun');
    }

    public function test_unauthorized_roles_cannot_access_manager_dashboard(): void
    {
        $this->actingAs($this->cashier)->get(route('manager.dashboard'))->assertStatus(403);
        $this->actingAs($this->customer)->get(route('manager.dashboard'))->assertStatus(403);
    }

    public function test_manager_dashboard_shows_belum_ditugaskan_when_employee_is_not_assigned(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Haircut',
            'branch_id' => $this->branch1->id,
        ]);
        $service = Service::create([
            'branch_id' => $this->branch1->id,
            'service_category_id' => $category->id,
            'name' => 'Potong Rambut',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        Booking::create([
            'branch_id' => $this->branch1->id,
            'user_id' => $this->customer->id,
            'service_id' => $service->id,
            'employee_id' => null, // Unassigned
            'booking_code' => 'BK-UNASSIGNED-1',
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '14:00:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Aktivitas Layanan & Antrean Hari Ini', false);
        $response->assertSee('Belum Ditugaskan');
    }

    public function test_manager_dashboard_does_not_contain_duplicate_header_navigation_buttons(): void
    {
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        // Header navigation buttons have been removed from the header partial
        $response->assertDontSee('href="'.route('employees.shifts.index').'" class="gap-1.5 text-xs"', false);
        $response->assertDontSee('href="'.route('reports.index').'" class="gap-1.5 text-xs"', false);
    }
}
