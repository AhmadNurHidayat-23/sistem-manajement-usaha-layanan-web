<?php

namespace Tests\Feature\Layout;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarHeaderRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $employee;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat SIMAUL',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Bambang Owner',
            'email' => 'owner@simaul.com',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Siti Manager',
            'email' => 'manager@simaul.com',
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Dewi Kasir',
            'email' => 'kasir@simaul.com',
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Joko Karyawan',
            'email' => 'karyawan@simaul.com',
        ]);
        $this->employee->branches()->attach($this->branch->id);
        Employee::create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'name' => 'Joko Karyawan',
            'phone' => '081299990001',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Andi Pelanggan',
            'email' => 'pelanggan@simaul.com',
        ]);
    }

    public function test_owner_sidebar_has_only_logout_at_bottom_and_header_has_search_notification_profile(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.dashboard'));

        $response->assertOk();

        // 1. Sidebar checks
        $response->assertSee('sidebar-footer-section', false);
        $response->assertSee('sidebar-logout-btn', false);
        $response->assertSee('Keluar');
        $response->assertDontSee('sidebar-user-info', false);

        // 2. Header checks
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="menu-search-wrapper"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('placeholder="Cari menu..."', false);
        $response->assertSee('rounded-full', false);
        $response->assertDontSee('id="menu-search-dropdown"', false);
        $response->assertSee('id="menu-search-feedback"', false);
        $response->assertSee('id="notification-bell-btn"', false);
        $response->assertSee('id="header-profile-menu"', false);
        $response->assertSee('Bambang Owner');

        // No role badge
        $content = $response->getContent();
        $this->assertStringNotContainsString('OWNER</x-badges.badge>', $content);
        $this->assertStringNotContainsString('<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-[#062072] text-white border-transparent">OWNER</span>', $content);

        // Search menus contain owner links
        $response->assertSee(route('branches.index'), false);
        $response->assertSee(route('catalog.services.index'), false);
        $response->assertSee(route('reports.index'), false);
    }

    public function test_manager_sidebar_and_header_match_redesign(): void
    {
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertOk();

        // Sidebar
        $response->assertSee('sidebar-logout-btn', false);
        $response->assertDontSee('sidebar-user-info', false);

        // Header
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('id="notification-bell-btn"', false);
        $response->assertSee('Siti Manager');

        // Search menus contain manager links
        $response->assertSee(route('employees.shifts.index'), false);
        $response->assertSee(route('inventori.index'), false);
    }

    public function test_cashier_sidebar_and_header_match_redesign(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));

        $response->assertOk();

        // Sidebar
        $response->assertSee('sidebar-logout-btn', false);
        $response->assertDontSee('sidebar-user-info', false);

        // Header
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('id="notification-bell-btn"', false);
        $response->assertSee('Dewi Kasir');

        // Search menus contain cashier links
        $response->assertSee(route('pos.index'), false);
        $response->assertSee(route('pos.transactions.index'), false);
        $response->assertSee(route('members.index'), false);
    }

    public function test_employee_sidebar_and_header_match_redesign(): void
    {
        $response = $this->actingAs($this->employee)->get(route('employee.dashboard'));

        $response->assertOk();

        // Sidebar
        $response->assertSee('sidebar-logout-btn', false);
        $response->assertDontSee('sidebar-user-info', false);

        // Header
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('id="notification-bell-btn"', false);
        $response->assertSee('Joko Karyawan');

        // Search menus contain employee links
        $response->assertSee(route('queue.index'), false);
        $response->assertSee(route('employees.my-shifts'), false);
        $response->assertSee(route('employees.my-commissions'), false);
    }

    public function test_customer_view_and_navigation_remain_unchanged(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.home'));

        $response->assertOk();
        $response->assertSee('id="floating-header"', false);
        $response->assertDontSee('id="menu-search-input"', false);
        $response->assertDontSee('sidebar-nav', false);
    }
}
