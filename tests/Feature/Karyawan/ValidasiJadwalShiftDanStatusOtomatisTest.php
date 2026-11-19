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
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiJadwalShiftDanStatusOtomatisTest extends TestCase
{
    use RefreshDatabase;

    protected User $managerBranch1;

    protected User $managerBranch2;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employeeA;

    protected Employee $employeeB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();

        $this->branch1 = Branch::create([
            'name' => 'SIMAUL Barbershop Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'SIMAUL Laundry Barat',
            'address' => 'Jl. Thamrin No. 2',
            'phone' => '0822222222',
            'is_active' => true,
        ]);

        $this->managerBranch1 = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Satu',
            'email' => 'manager1@simaul.com',
        ]);
        $this->managerBranch1->branches()->attach($this->branch1->id);

        $this->managerBranch2 = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Dua',
            'email' => 'manager2@simaul.com',
        ]);
        $this->managerBranch2->branches()->attach($this->branch2->id);

        $userA = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employeeA = Employee::create([
            'user_id' => $userA->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Karyawan A',
            'phone' => '0833333333',
            'status' => 'active',
        ]);

        $userB = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employeeB = Employee::create([
            'user_id' => $userB->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Karyawan B',
            'phone' => '0844444444',
            'status' => 'active',
        ]);
    }

    /**
     * 1. Jadwal untuk karyawan yang sama pada tanggal berbeda harus diizinkan meskipun jam kerjanya sama.
     */
    public function test_karyawan_sama_tanggal_berbeda_jam_sama_diizinkan(): void
    {
        // Shift tanggal 20 November 08:00 - 16:00
        $res1 = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $res1->assertRedirect();
        $res1->assertSessionHas('success');

        // Shift tanggal 21 November dengan jam kerja sama persis (08:00 - 16:00)
        $res2 = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-21',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $res2->assertRedirect();
        $res2->assertSessionHas('success');
        $res2->assertSessionMissing('error');

        $this->assertEquals(2, EmployeeShift::where('employee_id', $this->employeeA->id)->count());
    }

    /**
     * 2. Beberapa karyawan boleh bekerja pada shift, tanggal, dan jam yang sama.
     */
    public function test_beberapa_karyawan_boleh_bekerja_pada_shift_tanggal_dan_jam_sama(): void
    {
        // Jadwalkan Karyawan A
        $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ])->assertSessionHas('success');

        // Jadwalkan Karyawan B pada tanggal dan jam yang sama persis
        $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeB->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ])->assertSessionHas('success');

        $this->assertEquals(1, EmployeeShift::where('employee_id', $this->employeeA->id)->whereDate('shift_date', '2026-11-20')->count());
        $this->assertEquals(1, EmployeeShift::where('employee_id', $this->employeeB->id)->whereDate('shift_date', '2026-11-20')->count());
    }

    /**
     * 3. Bentrok hanya terjadi jika karyawan yang sama mempunyai dua rentang waktu kerja yang saling tumpang tindih.
     */
    public function test_bentrok_karyawan_sama_jika_rentang_waktu_tumpang_tindih(): void
    {
        // Shift 1: 08:00 - 14:00
        EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '14:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Shift 2 (bertumpukan 13:00 - 17:00) -> Ditolak
        $resOverlap = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '13:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $resOverlap->assertSessionHas('error', 'Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');

        // Shift 3 (bersebelahan tepat 14:00 - 18:00) -> Diterima
        $resAdjacent = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '14:00',
            'end_time' => '18:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $resAdjacent->assertSessionHas('success');
    }

    /**
     * 4. Saat mengedit jadwal, abaikan ID jadwal yang sedang diedit dari pemeriksaan bentrok.
     */
    public function test_edit_jadwal_mengabaikan_id_jadwal_sendiri(): void
    {
        $shift = EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $res = $this->actingAs($this->managerBranch1)->put(route('employees.shifts.update', $shift), [
            'shift_date' => '2026-11-20',
            'start_time' => '08:30',
            'end_time' => '16:30',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $res->assertSessionHas('success');
        $this->assertEquals('08:30', substr((string) $shift->fresh()->start_time, 0, 5));
    }

    /**
     * 5. Gunakan tanggal dan waktu lengkap dalam validasi, termasuk penanganan shift yang melewati tengah malam.
     */
    public function test_penanganan_shift_melewati_tengah_malam_dan_bentrok_lintas_hari(): void
    {
        // Shift 1: Malam 20 Nov 22:00 s/d 21 Nov 06:00 (durasi 8 jam)
        $resNight = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $resNight->assertSessionHas('success');

        // Shift 2 bertabrakan di pagi hari berikutnya: 21 Nov 04:00 - 10:00 -> Ditolak karena tumpang tindih
        $resClashNextDay = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-21',
            'start_time' => '04:00',
            'end_time' => '10:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $resClashNextDay->assertSessionHas('error', 'Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');

        // Shift 3 dimulai tepat setelah shift malam berakhir: 21 Nov 06:00 - 14:00 -> Diterima
        $resAfter = $this->actingAs($this->managerBranch1)->post(route('employees.shifts.store'), [
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-21',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
        $resAfter->assertSessionHas('success');
    }

    /**
     * 6 & 7. Shift yang sudah melewati jam selesai otomatis "Berakhir", shift mendatang tetap "Terjadwal".
     */
    public function test_shift_lewat_waktu_otomatis_berakhir_dan_shift_mendatang_tetap_terjadwal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 17:00:00', 'Asia/Jakarta'));

        // Shift masa lalu (sudah lewat jam selesai 16:00)
        $pastShift = EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Shift mendatang (mulai nanti malam 20:00 - 23:00)
        $futureShift = EmployeeShift::create([
            'employee_id' => $this->employeeB->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '20:00',
            'end_time' => '23:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $this->assertTrue($pastShift->isEnded());
        $this->assertEquals(StatusShiftKaryawan::Ended, $pastShift->effective_status);
        $this->assertEquals('Berakhir', $pastShift->effective_status->label());

        $this->assertFalse($futureShift->isEnded());
        $this->assertTrue($futureShift->isUpcoming());
        $this->assertEquals(StatusShiftKaryawan::Scheduled, $futureShift->effective_status);
        $this->assertEquals('Terjadwal', $futureShift->effective_status->label());

        // Cek rendering tampilan table
        $response = $this->actingAs($this->managerBranch1)->get(route('employees.shifts.index'));
        $response->assertOk();
        $response->assertSee('Berakhir');
        $response->assertSee('Terjadwal');

        Carbon::setTestNow();
    }

    /**
     * 8. Jangan otomatis mengubah status kehadiran menjadi Hadir atau Absen (status riil tetap dipertahankan).
     */
    public function test_status_kehadiran_riil_tetap_dipertahankan(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 18:00:00', 'Asia/Jakarta'));

        $shiftPresent = EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Present,
        ]);

        $shiftAbsent = EmployeeShift::create([
            'employee_id' => $this->employeeB->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Absent,
        ]);

        $this->assertEquals(StatusShiftKaryawan::Present, $shiftPresent->effective_status);
        $this->assertEquals('Hadir', $shiftPresent->effective_status->label());

        $this->assertEquals(StatusShiftKaryawan::Absent, $shiftAbsent->effective_status);
        $this->assertEquals('Absen', $shiftAbsent->effective_status->label());

        Carbon::setTestNow();
    }

    /**
     * 9. Shift yang sudah berakhir tidak boleh dihapus otomatis dan tetap tersimpan sebagai riwayat.
     */
    public function test_shift_berakhir_tidak_dihapus_dan_tersimpan_di_riwayat(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-22 10:00:00', 'Asia/Jakarta'));

        $shift = EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $this->assertDatabaseHas('employee_shifts', [
            'id' => $shift->id,
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);

        $resHistory = $this->actingAs($this->managerBranch1)->get(route('employees.shifts.index', ['scope' => 'history']));
        $resHistory->assertOk();
        $resHistory->assertSee($this->employeeA->name);
        $resHistory->assertSee('Berakhir');

        Carbon::setTestNow();
    }

    /**
     * 10. Batasi aksi absensi berdasarkan tanggal dan waktu shift serta kewenangan pengguna.
     */
    public function test_pembatasan_aksi_absensi_berdasarkan_waktu_dan_kewenangan(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 07:00:00', 'Asia/Jakarta'));

        // Shift hari ini yang baru mulai jam 10:00 (masih di masa depan)
        $futureShiftToday = EmployeeShift::create([
            'employee_id' => $this->employeeA->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => '2026-11-20',
            'start_time' => '10:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Coba tandai Hadir sebelum shift dimulai -> Ditolak
        $resEarly = $this->actingAs($this->managerBranch1)->patch(route('employees.shifts.status', $futureShiftToday), [
            'status' => StatusShiftKaryawan::Present->value,
        ]);
        $resEarly->assertSessionHas('error');

        // Manajer Cabang 2 (tidak berwenang di Cabang 1) -> 403 Forbidden
        $resUnauthorized = $this->actingAs($this->managerBranch2)->patch(route('employees.shifts.status', $futureShiftToday), [
            'status' => StatusShiftKaryawan::Present->value,
        ]);
        $resUnauthorized->assertForbidden();

        // Saat waktu telah memasuki jam kerja shift (misal 11:00)
        Carbon::setTestNow(Carbon::parse('2026-11-20 11:00:00', 'Asia/Jakarta'));

        $resAllowed = $this->actingAs($this->managerBranch1)->patch(route('employees.shifts.status', $futureShiftToday), [
            'status' => StatusShiftKaryawan::Present->value,
        ]);
        $resAllowed->assertSessionHas('success');
        $this->assertEquals(StatusShiftKaryawan::Present, $futureShiftToday->fresh()->status);

        Carbon::setTestNow();
    }
}
