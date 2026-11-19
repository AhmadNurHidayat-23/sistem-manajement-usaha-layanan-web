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

class HeaderDashboardTanpaPencarianTest extends TestCase
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
            'address' => 'Jl. Merdeka No. 1, Jakarta Pusat',
            'phone' => '081234567890',
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
            'name' => 'Bambang Owner',
            'email' => 'owner@simaul.com',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Siti Manajer',
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
    }

    public function test_seluruh_dashboard_internal_tidak_menampilkan_pencarian_menu(): void
    {
        $dashboards = [
            'owner' => [$this->owner, route('owner.dashboard'), 'Bambang Owner'],
            'manager' => [$this->manager, route('manager.dashboard'), 'Siti Manajer'],
            'cashier' => [$this->cashier, route('cashier.dashboard'), 'Dewi Kasir'],
            'employee' => [$this->employee, route('employee.dashboard'), 'Joko Karyawan'],
        ];

        foreach ($dashboards as $role => [$user, $url, $expectedName]) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk();

            // 1. Kolom pencarian "Cari menu..." dan wrapper tidak boleh tampil
            $response->assertDontSee('id="menu-search-input"', false);
            $response->assertDontSee('id="menu-search-wrapper"', false);
            $response->assertDontSee('Cari menu...');
            $response->assertDontSee('id="menu-search-clear"', false);
            $response->assertDontSee('id="menu-search-feedback"', false);

            // 2. Sisi kiri header: Jam & Tanggal real-time dan mobile toggle button tetap ada
            $response->assertSee('id="mobile-sidebar-toggle-btn"', false);
            $response->assertSee('id="header-realtime-clock"', false);
            $response->assertSee('id="realtime-clock-time"', false);
            $response->assertSee('id="realtime-clock-date"', false);

            // 3. Sisi kanan header: Lonceng notifikasi dan profil pengguna tetap ada
            $response->assertSee('id="notification-bell-btn"', false);
            $response->assertSee('id="header-profile-menu"', false);
            $response->assertSee('id="header-profile-btn"', false);
            $response->assertSee($expectedName);
        }
    }

    public function test_navbar_dashboard_menampilkan_hanya_foto_profil_lingkaran_tanpa_teks_nama_di_sebelahnya(): void
    {
        $dashboards = [
            'owner' => [$this->owner, route('owner.dashboard'), 'Bambang Owner'],
            'manager' => [$this->manager, route('manager.dashboard'), 'Siti Manajer'],
            'cashier' => [$this->cashier, route('cashier.dashboard'), 'Dewi Kasir'],
            'employee' => [$this->employee, route('employee.dashboard'), 'Joko Karyawan'],
        ];

        foreach ($dashboards as $role => [$user, $url, $expectedName]) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();
            $content = $response->getContent();

            // 1. Tombol profil berbentuk lingkaran w-9 h-9 rounded-full
            $this->assertMatchesRegularExpression(
                '/<button[^>]*id="header-profile-btn"[^>]*class="[^"]*rounded-full[^"]*"/',
                $content
            );

            // 2. Di dalam button profil TIDAK ADA teks nama akun (hanya foto / inisial)
            // Regex ekstrak inner HTML tombol header-profile-btn
            preg_match('/<button[^>]*id="header-profile-btn"[^>]*>(.*?)<\/button>/s', $content, $btnMatches);
            $this->assertNotEmpty($btnMatches, "Tombol header-profile-btn harus ditemukan untuk {$role}");
            $btnInner = $btnMatches[1];

            // Pastikan teks nama akun tidak ada di dalam button
            $this->assertStringNotContainsString(">{$expectedName}<", $btnInner);
            $this->assertStringNotContainsString('truncate leading-tight', $btnInner);

            // 3. Nama akun tetap tersedia di dalam menu dropdown profil
            $this->assertStringContainsString('id="header-profile-dropdown"', $content);
            $this->assertStringContainsString($expectedName, $content);

            // 4. Lonceng notifikasi tetap ada di sebelah profil dengan jarak rapi
            $response->assertSee('id="notification-bell-btn"', false);

            // 5. Tombol Dark Mode Dashboard Internal tersedia
            $response->assertSee('id="internal-theme-toggle-btn"', false);
            $response->assertSee('id="internal-theme-moon"', false);
            $response->assertSee('id="internal-theme-sun"', false);
        }
    }

    public function test_pencarian_modul_lain_tetap_berfungsi_dan_tidak_terhapus(): void
    {
        // Contoh: Pencarian pada halaman Laporan Keuangan (Filter Pencarian Tanggal/Periode)
        $responseReport = $this->actingAs($this->owner)->get(route('reports.index'));
        $responseReport->assertOk();
        $responseReport->assertSee('id="custom-period-dropdown-wrapper"', false);
        $responseReport->assertSee('id="btn-toggle-custom-period"', false);
        // Header pada halaman laporan tetap bersih tanpa "Cari menu..."
        $responseReport->assertDontSee('id="menu-search-input"', false);
        $responseReport->assertDontSee('Cari menu...');

        // Contoh: Pencarian pada halaman Anggota/Member Kasir
        $responseMember = $this->actingAs($this->cashier)->get(route('members.index'));
        $responseMember->assertOk();
        // Input pencarian tabel data member tetap ada
        $responseMember->assertSee('id="f_q"', false);
        $responseMember->assertSee('name="q"', false);
        $responseMember->assertSee('Cari kode member, nama, telepon, atau email...');
        // Header di atasnya tidak memiliki "Cari menu..."
        $responseMember->assertDontSee('id="menu-search-input"', false);
        $responseMember->assertDontSee('Cari menu...');
    }
}
