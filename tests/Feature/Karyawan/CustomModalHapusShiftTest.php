<?php

namespace Tests\Feature\Karyawan;

use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomModalHapusShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $unauthorizedManager;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Barbershop Pusat',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456780',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Laundry Selatan',
            'address' => 'Jl. Sudirman No. 20',
            'phone' => '08123456781',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Siti',
            'email' => 'manajer.siti@simaul.com',
        ]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->unauthorizedManager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Hendro',
            'email' => 'manajer.hendro@simaul.com',
        ]);
        $this->unauthorizedManager->branches()->attach($this->branch2->id);

        $userEmp = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Dedi Barber',
            'email' => 'dedi@simaul.com',
        ]);

        $this->employee = Employee::create([
            'user_id' => $userEmp->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Dedi Kurniawan',
            'phone' => '0811111111',
            'status' => 'active',
        ]);
    }

    /**
     * 1. Halaman /employee-shifts merender komponen modal custom konfirmasi hapus dan tidak menggunakan window.confirm bawaan.
     */
    public function test_halaman_shift_merender_modal_custom_konfirmasi_hapus(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('employees.shifts.index'));

        $response->assertOk();

        // Verifikasi keberadaan modal custom konfirmasi hapus
        $response->assertSee('id="modal-delete-shift"', false);
        $response->assertSee('id="form-delete-shift"', false);
        $response->assertSee('Hapus Jadwal Shift?');
        $response->assertSee('Apakah Anda yakin ingin menghapus jadwal shift ini? Tindakan ini tidak dapat dibatalkan.');
        $response->assertSee('id="btn-confirm-delete-shift"', false);
        $response->assertSee('Hapus Shift');
        $response->assertSee('data-modal-close="modal-delete-shift"', false);

        // Verifikasi bahwa tombol hapus pada tabel menggunakan data-action="delete-shift"
        $response->assertSee('data-action="delete-shift"', false);
        $response->assertSee('data-action-url="'.route('employees.shifts.destroy', $shift).'"', false);

        // Pastikan tidak ada dialog confirm bawaan browser Chrome pada tabel
        $response->assertDontSee("confirm('Hapus shift ini?')", false);
        $response->assertDontSee('confirm("Hapus shift ini?")', false);
    }

    /**
     * 2. Penghapusan jadwal shift berhasil dieksekusi via DELETE endpoint yang sudah ada.
     */
    public function test_penghapusan_shift_berhasil_via_endpoint(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->manager)
            ->delete(route('employees.shifts.destroy', $shift));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Jadwal shift berhasil dihapus.');

        $this->assertDatabaseMissing('employee_shifts', [
            'id' => $shift->id,
        ]);
    }

    /**
     * 3. Otorisasi cabang tetap ditegakkan saat menghapus shift (manajer cabang lain ditolak).
     */
    public function test_otorisasi_cabang_tetap_ditegakkan_saat_hapus_shift(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->unauthorizedManager)
            ->delete(route('employees.shifts.destroy', $shift));

        $response->assertForbidden();

        $this->assertDatabaseHas('employee_shifts', [
            'id' => $shift->id,
        ]);
    }
}
