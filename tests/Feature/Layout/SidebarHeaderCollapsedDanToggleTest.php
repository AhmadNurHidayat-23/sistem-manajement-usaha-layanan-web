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

class SidebarHeaderCollapsedDanToggleTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $employee;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Pusat',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '081122334455',
            'service_mode' => 'appointment',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Owner SIMAUL',
            'email' => 'owner@simaul.com',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager SIMAUL',
            'email' => 'manager@simaul.com',
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir SIMAUL',
            'email' => 'kasir@simaul.com',
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Karyawan SIMAUL',
            'email' => 'karyawan@simaul.com',
        ]);
        $this->employee->branches()->attach($this->branch->id);
        Employee::create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'name' => 'Karyawan SIMAUL',
            'phone' => '081299990001',
            'status' => 'active',
        ]);
    }

    public function test_sidebar_toggle_mechanism_per_latest_specification(): void
    {
        $users = [
            'owner' => [$this->owner, route('owner.dashboard')],
            'manager' => [$this->manager, route('manager.dashboard')],
            'cashier' => [$this->cashier, route('cashier.dashboard')],
            'employee' => [$this->employee, route('employee.dashboard')],
        ];

        foreach ($users as $role => [$user, $url]) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk();

            // 1. Topbar TIDAK memiliki tombol toggle desktop
            $response->assertDontSee('id="desktop-sidebar-toggle-btn"', false);

            // 2. Topbar tetap memiliki tombol toggle mobile drawer
            $response->assertSee('id="mobile-sidebar-toggle-btn"', false);

            // 3. Header sidebar memiliki logo full SIMAUL
            $response->assertSee('sidebar-brand-full', false);
            $response->assertSee('images/brand/logo-header.png');

            // 4. Header sidebar memiliki tombol toggle penutup di sebelah kanan (hidden lg:flex)
            $response->assertSee('sidebar-toggle-close', false);
            $response->assertSee('title="Kecilkan Sidebar"', false);

            // 5. Header sidebar memiliki logo mini sebagai tombol pembuka saat collapsed
            $response->assertSee('sidebar-brand-mini', false);
            $response->assertSee('images/brand/favicon.png');
            $response->assertSee('title="Buka Menu Navigasi"', false);

            // 6. Header sidebar memiliki tombol tutup mobile drawer
            $response->assertSee('sidebar-close-btn', false);

            // 7. Garis horizontal pembatas di bawah header dan di atas tombol keluar dihapus
            $response->assertDontSee('sidebar-header h-[72px] flex items-center justify-between px-5 sm:px-6 border-b', false);
            $response->assertDontSee('sidebar-footer-section px-3 pb-3 pt-2.5 border-t', false);
            $response->assertSee('sidebar-header h-[72px] flex items-center justify-between px-5 sm:px-6 bg-white shrink-0', false);
            $response->assertSee('sidebar-footer-section px-3 pb-3 pt-2.5 bg-white shrink-0', false);
        }
    }

    public function test_tombol_pencarian_data_menggunakan_label_cari(): void
    {
        // Kasir: Member
        $responseMember = $this->actingAs($this->cashier)->get(route('members.index'));
        $responseMember->assertOk();
        $responseMember->assertSee('<span>Cari</span>', false);
        $responseMember->assertDontSee('<span>Filter</span>', false);

        // Kasir: Riwayat Transaksi
        $responseTrx = $this->actingAs($this->cashier)->get(route('pos.transactions.index'));
        $responseTrx->assertOk();
        $responseTrx->assertSee('<span>Cari</span>', false);
        $responseTrx->assertDontSee('<span>Filter</span>', false);

        // Manager: Booking
        $responseBooking = $this->actingAs($this->manager)->get(route('bookings.index'));
        $responseBooking->assertOk();
        $responseBooking->assertSee('<span>Cari</span>', false);
        $responseBooking->assertDontSee('<span>Filter</span>', false);

        // Owner: Layanan
        $responseService = $this->actingAs($this->owner)->get(route('catalog.services.index'));
        $responseService->assertOk();
        $responseService->assertSee('<span>Cari</span>', false);
        $responseService->assertDontSee('<span>Filter</span>', false);
    }

    public function test_sidebar_collapsed_state_via_cookie_applies_collapsed_classes(): void
    {
        $response = $this->actingAs($this->owner)
            ->withUnencryptedCookie('sidebar_collapsed', '1')
            ->get(route('owner.dashboard'));

        $response->assertOk();
        $response->assertSee('class="sidebar-nav fixed top-0 left-0 bg-white text-[#101828] flex flex-col h-screen z-30 select-none border-r border-[#E4E7EC] collapsed"', false);
        $response->assertSee('class="main-area flex flex-col h-screen overflow-hidden sidebar-collapsed"', false);
    }
}
