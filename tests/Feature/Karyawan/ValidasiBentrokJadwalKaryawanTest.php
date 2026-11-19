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

class ValidasiBentrokJadwalKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employee1;

    protected Employee $employee2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Barbershop (Cabang Pusat)',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456780',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Laundry (Cabang Selatan)',
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

        $userEmp1 = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Dedi Barber',
            'email' => 'dedi@simaul.com',
        ]);

        $this->employee1 = Employee::create([
            'user_id' => $userEmp1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Dedi Kurniawan',
            'phone' => '0811111111',
            'status' => 'active',
        ]);

        $userEmp2 = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Bayu Barber',
            'email' => 'bayu@simaul.com',
        ]);

        $this->employee2 = Employee::create([
            'user_id' => $userEmp2->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Bayu Pratama',
            'phone' => '0822222222',
            'status' => 'active',
        ]);
    }

    /**
     * 1. Beberapa karyawan boleh bekerja pada hari, jam, dan shift yang sama.
     */
    public function test_beberapa_karyawan_boleh_bekerja_pada_hari_jam_dan_shift_yang_sama(): void
    {
        // Jadwalkan Karyawan 1 (Dedi)
        $res1 = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $res1->assertRedirect();
        $res1->assertSessionHas('success');

        // Jadwalkan Karyawan 2 (Bayu) pada HARI, JAM, dan CABANG yang SAMA
        $res2 = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee2->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $res2->assertRedirect();
        $res2->assertSessionHas('success');
        $res2->assertSessionMissing('error');

        // Pastikan kedua shift tersimpan dengan benar di database
        $s1 = EmployeeShift::where('employee_id', $this->employee1->id)
            ->whereDate('shift_date', '2026-11-20')
            ->first();
        $this->assertNotNull($s1);
        $this->assertStringStartsWith('08:00', (string) $s1->start_time);
        $this->assertStringStartsWith('16:00', (string) $s1->end_time);

        $s2 = EmployeeShift::where('employee_id', $this->employee2->id)
            ->whereDate('shift_date', '2026-11-20')
            ->first();
        $this->assertNotNull($s2);
        $this->assertStringStartsWith('08:00', (string) $s2->start_time);
        $this->assertStringStartsWith('16:00', (string) $s2->end_time);
    }

    /**
     * 2. Validasi bentrok hanya berlaku jika karyawan yang SAMA memiliki dua jadwal bertabrakan pada hari yang sama.
     */
    public function test_karyawan_yang_sama_bentrok_pada_hari_dan_jam_yang_sama_ditolak(): void
    {
        // Buat shift pertama untuk Dedi (08:00 - 16:00)
        EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Coba jadwalkan shift kedua untuk Dedi yang bertabrakan (10:00 - 15:00)
        $response = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '10:00',
            'end_time' => '15:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');

        // Hanya ada 1 shift untuk Dedi pada hari tersebut
        $this->assertEquals(1, EmployeeShift::where('employee_id', $this->employee1->id)->whereDate('shift_date', '2026-11-20')->count());
    }

    /**
     * 3. Jadwal yang bersebelahan/berbatasan waktu tepat tidak dianggap bentrok.
     */
    public function test_karyawan_yang_sama_pada_jam_bersebelahan_tidak_dianggap_bentrok(): void
    {
        // Shift 1: 08:00 - 12:00
        EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Shift 2 tepat setelahnya: 12:00 - 16:00
        $response = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '12:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $this->assertEquals(2, EmployeeShift::where('employee_id', $this->employee1->id)->whereDate('shift_date', '2026-11-20')->count());
    }

    /**
     * 4. Jadwal yang berbeda hari tidak dianggap bentrok.
     */
    public function test_karyawan_yang_sama_pada_hari_yang_berbeda_tidak_dianggap_bentrok(): void
    {
        // Shift hari ini: 08:00 - 16:00
        EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Shift besok: jam yang sama persis (08:00 - 16:00) pada hari berbeda
        $response = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-21',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $sTomorrow = EmployeeShift::where('employee_id', $this->employee1->id)
            ->whereDate('shift_date', '2026-11-21')
            ->first();
        $this->assertNotNull($sTomorrow);
        $this->assertStringStartsWith('08:00', (string) $sTomorrow->start_time);
        $this->assertStringStartsWith('16:00', (string) $sTomorrow->end_time);
    }

    /**
     * 5. Saat mengedit jadwal, jangan bandingkan jadwal dengan dirinya sendiri.
     */
    public function test_edit_jadwal_tidak_membandingkan_jadwal_dengan_dirinya_sendiri(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Simpan tanpa mengubah jam atau hanya geser 30 menit
        $response = $this->actingAs($this->manager)->put(route('employees.shifts.update', $shift), [
            'shift_date' => '2026-11-20',
            'start_time' => '08:30',
            'end_time' => '16:30',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $this->assertStringStartsWith('08:30', (string) $shift->fresh()->start_time);
        $this->assertStringStartsWith('16:30', (string) $shift->fresh()->end_time);
    }

    /**
     * 6. Saat mengedit jadwal, bentrok tetap dicegah jika bertabrakan dengan shift LAIN karyawan yang sama.
     */
    public function test_edit_jadwal_ke_jam_yang_bentrok_dengan_shift_lain_karyawan_yang_sama_ditolak(): void
    {
        // Shift 1: 08:00 - 12:00
        $shift1 = EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Shift 2: 13:00 - 17:00
        $shift2 = EmployeeShift::create([
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Coba edit Shift 2 agar bertabrakan dengan Shift 1 (10:00 - 14:00)
        $response = $this->actingAs($this->manager)->put(route('employees.shifts.update', $shift2), [
            'shift_date' => '2026-11-20',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');

        $this->assertEquals('13:00:00', $shift2->fresh()->start_time);
    }

    /**
     * 7. Otorisasi cabang dan batasan durasi 8 jam tetap ditegakkan.
     */
    public function test_pembatasan_cabang_dan_aturan_durasi_tetap_ditegakkan(): void
    {
        // Manajer hanya memiliki akses ke Branch 1, coba buat di Branch 2 -> ditolak
        $responseBranch = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch2->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $responseBranch->assertForbidden();

        // Durasi 9 jam -> ditolak oleh validasi
        $responseDuration = $this->actingAs($this->manager)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employee1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $responseDuration->assertSessionHasErrors('end_time');
    }
}
