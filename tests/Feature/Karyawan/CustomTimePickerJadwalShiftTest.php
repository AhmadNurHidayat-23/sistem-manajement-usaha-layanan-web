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
use Tests\TestCase;

class CustomTimePickerJadwalShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Branch $branch;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $managerRole = Role::create([
            'name' => UserRole::Manager->value,
            'label' => 'Manager',
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Barbershop Test',
            'display_name' => 'Barbershop & Studio Test',
            'service_type' => 'barbershop',
            'address' => 'Jl. Uji No. 123',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->manager = User::create([
            'name' => 'Budi Manajer',
            'email' => 'manajer.test@simaul.com',
            'password' => bcrypt('password123'),
            'role_id' => $managerRole->id,
            'branch_id' => $this->branch->id,
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $empUser = User::create([
            'name' => 'Andi Barber',
            'email' => 'andi.barber@simaul.com',
            'password' => bcrypt('password123'),
            'role_id' => Role::create(['name' => UserRole::Employee->value, 'label' => 'Employee'])->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $empUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Andi Barber',
            'phone' => '081299998888',
            'status' => 'active',
        ]);
    }

    public function test_halaman_shift_merender_custom_time_picker_pada_modal_tambah_dan_edit(): void
    {
        // Buat satu shift dummy untuk memastikan tabel memunculkan tombol edit
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('employees.shifts.index'));

        $response->assertOk();

        // Verifikasi komponen custom time picker pada Modal Tambah Shift
        $response->assertSee('modal-create-shift');
        $response->assertSee('name="start_time"', false);
        $response->assertSee('name="end_time"', false);
        $response->assertSee('data-custom-time-picker="true"', false);

        // Verifikasi komponen custom time picker pada Modal Edit Shift
        $response->assertSee('modal-edit-shift');
        $response->assertSee('form-edit-shift');
        $response->assertSee('id="edit_shift_start_time"', false);
        $response->assertSee('id="edit_shift_end_time"', false);

        // Verifikasi tombol edit pada tabel shift
        $response->assertSee('data-action="edit-shift"', false);
    }

    public function test_tambah_jadwal_shift_berhasil_menyimpan_format_24_jam(): void
    {
        $shiftDate = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->manager)
            ->post(route('employees.shifts.store'), [
                'employee_id' => $this->employee->id,
                'branch_id' => $this->branch->id,
                'shift_date' => $shiftDate,
                'start_time' => '08:00',
                'end_time' => '16:00',
                'status' => 'scheduled',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employee_shifts', [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'status' => 'scheduled',
        ]);

        $savedShift = EmployeeShift::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($savedShift);
        $this->assertEquals('08:00', substr($savedShift->start_time, 0, 5));
        $this->assertEquals('16:00', substr($savedShift->end_time, 0, 5));
    }

    public function test_edit_jadwal_shift_berhasil_memperbarui_jam_mulai_dan_selesai(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->manager)
            ->put(route('employees.shifts.update', $shift), [
                'shift_date' => $shift->shift_date,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'status' => 'scheduled',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shift->refresh();
        $this->assertEquals('09:00', substr($shift->start_time, 0, 5));
        $this->assertEquals('17:00', substr($shift->end_time, 0, 5));
    }

    public function test_validasi_durasi_maksimal_8_jam_tetap_ditegakkan(): void
    {
        $shiftDate = Carbon::tomorrow()->toDateString();

        // 08:00 ke 17:00 adalah 9 jam (melebihi batas 8 jam)
        $response = $this->actingAs($this->manager)
            ->post(route('employees.shifts.store'), [
                'employee_id' => $this->employee->id,
                'branch_id' => $this->branch->id,
                'shift_date' => $shiftDate,
                'start_time' => '08:00',
                'end_time' => '17:00',
                'status' => 'scheduled',
            ]);

        $response->assertSessionHasErrors(['end_time']);
        $this->assertDatabaseMissing('employee_shifts', [
            'employee_id' => $this->employee->id,
            'shift_date' => $shiftDate,
            'start_time' => '08:00:00',
        ]);
    }

    public function test_validasi_jam_selesai_harus_setelah_jam_mulai(): void
    {
        $shiftDate = Carbon::tomorrow()->toDateString();

        // 16:00 ke 08:00 terbalik
        $response = $this->actingAs($this->manager)
            ->post(route('employees.shifts.store'), [
                'employee_id' => $this->employee->id,
                'branch_id' => $this->branch->id,
                'shift_date' => $shiftDate,
                'start_time' => '16:00',
                'end_time' => '08:00',
                'status' => 'scheduled',
            ]);

        $response->assertSessionHasErrors(['end_time']);
    }
}
