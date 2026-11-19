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

class InternalDashboardNavbarLocaleAndThemeTest extends TestCase
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

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id, 'name' => 'Bambang Owner']);
        $this->manager = User::factory()->create(['role_id' => $managerRole->id, 'name' => 'Siti Manajer']);
        $this->manager->branches()->attach($this->branch->id);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id, 'name' => 'Dewi Kasir']);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employee = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Joko Karyawan']);
        $this->employee->branches()->attach($this->branch->id);
        Employee::create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'name' => 'Joko Karyawan',
            'position' => 'Senior Stylist',
            'is_active' => true,
        ]);
    }

    public function test_all_four_internal_dashboards_render_locale_switch_and_circular_dark_mode(): void
    {
        $dashboards = [
            'owner' => [$this->owner, route('owner.dashboard')],
            'manager' => [$this->manager, route('manager.dashboard')],
            'cashier' => [$this->cashier, route('cashier.dashboard')],
            'employee' => [$this->employee, route('employee.dashboard')],
        ];

        foreach ($dashboards as $role => [$user, $url]) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();
            $content = $response->getContent();

            // 1. Tombol bahasa ID 🇮🇩 tersedia
            $this->assertStringContainsString('btn-switch-locale', $content, "Locale switch button missing on {$role} dashboard");
            $this->assertStringContainsString('ID 🇮🇩', $content);

            // 2. Tombol Dark Mode berbentuk lingkaran sempurna (rounded-full)
            $this->assertMatchesRegularExpression(
                '/<button[^>]*id="internal-theme-toggle-btn"[^>]*class="[^"]*rounded-full[^"]*"/',
                $content,
                "Internal theme toggle button must be rounded-full on {$role} dashboard"
            );

            // 3. Ikon bulan dan matahari di dalam tombol Dark Mode
            $this->assertStringContainsString('id="internal-theme-moon"', $content);
            $this->assertStringContainsString('id="internal-theme-sun"', $content);

            // 4. Urutan kontrol di navbar kanan:
            // Tombol bahasa < Lonceng notifikasi < Tombol Dark Mode < Profil
            $posLocale = strpos($content, 'btn-switch-locale');
            $posNotif = strpos($content, 'id="notification-bell-btn"');
            $posTheme = strpos($content, 'id="internal-theme-toggle-btn"');
            $posProfile = strpos($content, 'id="header-profile-btn"');

            $this->assertTrue($posLocale !== false, "Locale switch must exist on {$role}");
            $this->assertTrue($posNotif !== false, "Notification bell must exist on {$role}");
            $this->assertTrue($posTheme !== false, "Theme toggle must exist on {$role}");
            $this->assertTrue($posProfile !== false, "Profile button must exist on {$role}");

            $this->assertTrue($posLocale < $posNotif, "Locale switch must be to the left of notification bell on {$role}");
            $this->assertTrue($posNotif < $posTheme, "Notification bell must be to the left of Dark Mode button on {$role}");
            $this->assertTrue($posTheme < $posProfile, "Dark Mode button must be to the left of profile button on {$role}");
        }
    }

    public function test_internal_dashboards_switch_language_and_translate_sidebar_menus(): void
    {
        // 1. Bahasa Indonesia: Sidebar Owner menampilkan 'Utama', 'Manajemen Bisnis', 'Cabang', 'Laporan Keuangan'
        $responseId = $this->actingAs($this->owner)->withSession(['locale' => 'id'])->get(route('owner.dashboard'));
        $responseId->assertOk();
        $responseId->assertSee('ID 🇮🇩');
        $responseId->assertSee('Utama');
        $responseId->assertSee('Manajemen Bisnis');
        $responseId->assertSee('Cabang');
        $responseId->assertSee('Laporan Keuangan');

        // 2. Bahasa Inggris: Sidebar Owner menampilkan 'Main', 'Business Management', 'Branches', 'Financial Reports'
        $responseEn = $this->actingAs($this->owner)->withSession(['locale' => 'en'])->get(route('owner.dashboard'));
        $responseEn->assertOk();
        $responseEn->assertSee('EN 🇬🇧');
        $responseEn->assertSee('Main');
        $responseEn->assertSee('Business Management');
        $responseEn->assertSee('Branches');
        $responseEn->assertSee('Financial Reports');

        // 3. Bahasa Inggris pada Kasir: 'Operations', 'Shift Schedule' / 'Cashier Shift', 'Members'
        $kasirEn = $this->actingAs($this->cashier)->withSession(['locale' => 'en'])->get(route('cashier.dashboard'));
        $kasirEn->assertOk();
        $kasirEn->assertSee('EN 🇬🇧');
        $kasirEn->assertSee('Operations');
        $kasirEn->assertSee('Cashier Shift');
        $kasirEn->assertSee('Members');
    }
}
