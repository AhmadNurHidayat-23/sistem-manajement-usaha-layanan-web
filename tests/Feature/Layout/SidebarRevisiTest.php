<?php

namespace Tests\Feature\Layout;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarRevisiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        $branch = Branch::create([
            'name' => 'Cabang Uji Layout',
            'address' => 'Jl. Layout No. 10',
            'phone' => '081234567899',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Owner SIMAUL',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager SIMAUL',
        ]);
        $this->manager->branches()->attach($branch->id);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir SIMAUL',
        ]);
        $this->cashier->branches()->attach($branch->id);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Karyawan SIMAUL',
        ]);
        $this->employee->branches()->attach($branch->id);
    }

    public function test_desktop_sidebar_toggle_is_in_topbar_and_cleaned_from_sidebar_header_for_all_roles(): void
    {
        $users = [
            [$this->owner, route('owner.dashboard')],
            [$this->manager, route('manager.dashboard')],
            [$this->cashier, route('cashier.dashboard')],
            [$this->employee, route('employee.dashboard')],
        ];

        foreach ($users as [$user, $url]) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();

            // 1. Desktop toggle button exists in Topbar
            $response->assertSee('id="desktop-sidebar-toggle-btn"', false);
            $response->assertSee('sidebar-toggle-btn hidden lg:flex', false);

            // 2. Sidebar section dividers exist between menu groups
            $response->assertSee('sidebar-section-divider', false);

            // 3. Mini logo brand exists for collapsed state
            $response->assertSee('sidebar-brand-mini', false);

            // 4. Sidebar header contains close button for mobile but NO legacy sidebar-toggle-btn inside sidebar-header
            $html = $response->getContent();
            preg_match('/<div class="sidebar-header[^"]*">(.*?)<\/div>/s', $html, $matches);
            $this->assertNotEmpty($matches, "Sidebar header must exist for user {$user->name}");
            $this->assertStringNotContainsString('sidebar-toggle-btn', $matches[1], "Legacy sidebar-toggle-btn must not exist inside sidebar-header for user {$user->name}");
        }
    }
}
