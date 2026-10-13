<?php

namespace Tests\Feature\Karyawan;

use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanAkunDanNamaUsahaBersihTest extends TestCase
{
    use RefreshDatabase;

    protected Role $employeeRole;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Branch $branch3;

    protected User $userDedi;

    protected User $userAgus;

    protected User $userFajar;

    protected Employee $employeeDedi;

    protected Employee $employeeAgus;

    protected Employee $employeeFajar;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => UserRole::Owner->value]);
        Role::firstOrCreate(['name' => UserRole::Manager->value]);
        Role::firstOrCreate(['name' => UserRole::Cashier->value]);
        $this->employeeRole = Role::firstOrCreate(['name' => UserRole::Employee->value]);
        Role::firstOrCreate(['name' => UserRole::Customer->value]);

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Sudirman No. 1, Jakarta Pusat',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. Senopati No. 22, Jakarta Selatan',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $this->branch3 = Branch::create([
            'name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)',
            'address' => 'Jl. Panjang No. 88, Jakarta Barat',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        // Employee 1 (Barbershop)
        $this->userDedi = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
        ]);
        $this->employeeDedi = Employee::create([
            'user_id' => $this->userDedi->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Dedi Kurniawan',
            'status' => 'active',
        ]);

        // Employee 2 (Laundry)
        $this->userAgus = User::create([
            'name' => 'Agus Santoso',
            'email' => 'karyawan.laundry@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
        ]);
        $this->employeeAgus = Employee::create([
            'user_id' => $this->userAgus->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Agus Santoso',
            'status' => 'active',
        ]);

        // Employee 3 (Auto Care)
        $this->userFajar = User::create([
            'name' => 'Fajar Hidayat',
            'email' => 'karyawan.autocare@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->employeeRole->id,
        ]);
        $this->employeeFajar = Employee::create([
            'user_id' => $this->userFajar->id,
            'branch_id' => $this->branch3->id,
            'name' => 'Fajar Hidayat',
            'status' => 'active',
        ]);
    }

    public function test_business_display_names_are_strictly_three_clean_names(): void
    {
        $this->assertSame('Barbershop & Studio', $this->branch1->display_name);
        $this->assertSame('Laundry & Shoe Care', $this->branch2->display_name);
        $this->assertSame('Auto Care & Detailing', $this->branch3->display_name);

        // Address physical locations are preserved
        $this->assertStringContainsString('Jakarta Pusat', $this->branch1->address);
        $this->assertStringContainsString('Jakarta Selatan', $this->branch2->address);
        $this->assertStringContainsString('Jakarta Barat', $this->branch3->address);
    }

    public function test_employee_demo_logins_without_numeric_suffixes(): void
    {
        $credentials = [
            'karyawan@simaul.com' => $this->userDedi->id,
            'karyawan.barbershop@simaul.com' => $this->userDedi->id,
            'dedi.barbershop@simaul.com' => $this->userDedi->id,
            'karyawan.laundry@simaul.com' => $this->userAgus->id,
            'agus.laundry@simaul.com' => $this->userAgus->id,
            'karyawan.autocare@simaul.com' => $this->userFajar->id,
            'fajar.autocare@simaul.com' => $this->userFajar->id,
        ];

        foreach ($credentials as $email => $expectedUserId) {
            $response = $this->post(route('login'), [
                'email' => $email,
                'password' => 'password123',
            ]);

            $response->assertRedirect(route('employee.dashboard'));
            $this->assertAuthenticatedAs(User::find($expectedUserId));
            $this->post(route('logout'));
        }
    }

    public function test_employee_dashboard_displays_clean_business_name(): void
    {
        EmployeeShift::create([
            'employee_id' => $this->employeeDedi->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->userDedi)->get(route('employee.dashboard'));
        $response->assertOk();
        $response->assertSee('Barbershop & Studio');
        $response->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $response->assertDontSee('(Cabang Pusat)');
    }

    public function test_landing_page_pilih_cabang_shows_clean_business_names(): void
    {
        $response = $this->get(route('customer.home'));
        $response->assertOk();

        $response->assertSee('Barbershop & Studio');
        $response->assertSee('Laundry & Shoe Care');
        $response->assertSee('Auto Care & Detailing');

        $response->assertDontSee('SIMAUL Barbershop & Studio (Cabang Pusat)');
        $response->assertDontSee('SIMAUL Laundry & Shoe Care (Cabang Selatan)');
        $response->assertDontSee('SIMAUL Auto Care & Detailing (Cabang Barat)');
        $response->assertDontSee('(Cabang Pusat)');
        $response->assertDontSee('(Cabang Selatan)');
        $response->assertDontSee('(Cabang Barat)');
    }
}
