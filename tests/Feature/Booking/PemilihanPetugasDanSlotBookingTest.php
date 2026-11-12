<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\EmployeeSkill;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemilihanPetugasDanSlotBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employee1Branch1;

    protected Employee $employee2Branch1;

    protected Employee $employeeBranch2;

    protected Service $service30Min;

    protected Service $service240Min;

    protected string $testDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->testDate = now()->addDays(2)->toDateString();

        // Cabang 1: Buka 09:00 - 21:00, kapasitas 2
        $this->branch1 = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Thamrin No. 10',
            'phone' => '021-111111',
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'service_mode' => 'both',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        // Cabang 2: Buka 08:00 - 17:00, kapasitas 2
        $this->branch2 = Branch::create([
            'name' => 'Cabang Barat',
            'address' => 'Jl. Panjang No. 20',
            'phone' => '021-222222',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'service_mode' => 'both',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Perawatan',
            'description' => 'Layanan perawatan',
        ]);

        $this->service30Min = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cukur & Rapikan Jenggot',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->service240Min = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Helm & Detailing',
            'price' => 150000,
            'estimated_duration' => 240, // 4 jam
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $user1 = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->employee1Branch1 = Employee::create([
            'user_id' => $user1->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Santoso',
            'phone' => '08111111111',
            'status' => 'active',
        ]);

        $user2 = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->employee2Branch1 = Employee::create([
            'user_id' => $user2->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Gilang Ramadhan',
            'phone' => '08122222222',
            'status' => 'active',
        ]);

        $user3 = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->employeeBranch2 = Employee::create([
            'user_id' => $user3->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Eko Prasetyo',
            'phone' => '08133333333',
            'status' => 'active',
        ]);

        // Shift untuk Budi Santoso di Cabang 1
        EmployeeShift::create([
            'employee_id' => $this->employee1Branch1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $this->testDate,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Gilang Ramadhan TIDAK memiliki shift pada $this->testDate (petugas libur)

        $this->customer = User::factory()->create(['role_id' => $customerRole?->id]);
    }

    /**
     * 1 & 2. Satu cabang memiliki beberapa petugas, dan hanya petugas cabang tersebut yang muncul.
     */
    public function test_branch_data_only_returns_employees_assigned_to_branch(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', ['branch_id' => $this->branch1->id])
        );

        $response->assertOk();
        $employeeIds = collect($response->json('employees'))->pluck('id')->all();

        $this->assertContains($this->employee1Branch1->id, $employeeIds);
        $this->assertContains($this->employee2Branch1->id, $employeeIds);
        // Eko Prasetyo dari Cabang 2 tidak boleh muncul di Cabang 1
        $this->assertNotContains($this->employeeBranch2->id, $employeeIds);
    }

    /**
     * 3. Petugas memiliki shift dan slot tersedia berlabel 'Tersedia'.
     */
    public function test_slots_available_when_employee_has_scheduled_shift(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'employee_id' => $this->employee1Branch1->id,
                'service_id' => $this->service30Min->id,
            ])
        );

        $response->assertOk();
        $response->assertJson(['is_open' => true, 'employee_off_duty' => false]);

        $slots = $response->json('slots');
        $slot1000 = collect($slots)->firstWhere('time', '10:00');

        $this->assertNotNull($slot1000);
        $this->assertEquals('available', $slot1000['status']);
        $this->assertEquals('available', $slot1000['reason']);
        $this->assertEquals('Tersedia', $slot1000['label']);
        $this->assertTrue($slot1000['is_available']);
    }

    /**
     * 4. Petugas tidak memiliki shift -> seluruh slot berlabel 'Petugas Libur' dan employee_off_duty = true.
     */
    public function test_slots_show_petugas_libur_when_employee_has_no_shift(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'employee_id' => $this->employee2Branch1->id, // Gilang tidak punya shift
                'service_id' => $this->service30Min->id,
            ])
        );

        $response->assertOk();
        $response->assertJson([
            'is_open' => true,
            'employee_off_duty' => true,
        ]);

        $slots = $response->json('slots');
        $slot1000 = collect($slots)->firstWhere('time', '10:00');

        $this->assertNotNull($slot1000);
        $this->assertEquals('outside_shift', $slot1000['status']);
        $this->assertEquals('employee_no_shift', $slot1000['reason']);
        $this->assertEquals('Petugas Libur', $slot1000['label']);
        $this->assertFalse($slot1000['is_available']);
    }

    /**
     * 5. Jadwal sudah penuh -> label 'Penuh' dan reason 'capacity_full'.
     */
    public function test_slots_show_penuh_when_branch_capacity_is_reached(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);

        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'service_id' => $this->service30Min->id,
            ])
        );

        $slots = $response->json('slots');
        $slot1000 = collect($slots)->firstWhere('time', '10:00');

        $this->assertNotNull($slot1000);
        $this->assertEquals('full', $slot1000['status']);
        $this->assertEquals('capacity_full', $slot1000['reason']);
        $this->assertEquals('Penuh', $slot1000['label']);
        $this->assertFalse($slot1000['is_available']);
    }

    /**
     * 6. Petugas memiliki booking lain yang waktunya bentrok -> label 'Petugas Tidak Tersedia' dan reason 'employee_conflict'.
     */
    public function test_slots_show_petugas_tidak_tersedia_when_employee_has_clashing_booking(): void
    {
        // Kapasitas cabang 10, tapi petugas Budi sudah dibooking jam 11:00
        $this->branch1->update(['max_slot_capacity' => 10]);

        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'employee_id' => $this->employee1Branch1->id,
            'booking_date' => $this->testDate,
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'employee_id' => $this->employee1Branch1->id,
                'service_id' => $this->service30Min->id,
            ])
        );

        $slots = $response->json('slots');
        $slot1100 = collect($slots)->firstWhere('time', '11:00');

        $this->assertNotNull($slot1100);
        $this->assertEquals('full', $slot1100['status']);
        $this->assertEquals('employee_conflict', $slot1100['reason']);
        $this->assertEquals('Petugas Tidak Tersedia', $slot1100['label']);
        $this->assertFalse($slot1100['is_available']);
    }

    /**
     * 7. Durasi layanan (4 jam) melewati jam operasional cabang (tutup 21:00).
     */
    public function test_slots_outside_operating_hours_show_proper_reason_and_label(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'service_id' => $this->service240Min->id, // 4 jam
            ])
        );

        $slots = $response->json('slots');

        // 17:00 + 4 jam = 21:00 (tepat jam tutup) -> Tersedia
        $slot1700 = collect($slots)->firstWhere('time', '17:00');
        $this->assertTrue($slot1700['is_available']);

        // 18:00 + 4 jam = 22:00 (> 21:00) -> Di Luar Jam Operasional
        $slot1800 = collect($slots)->firstWhere('time', '18:00');
        $this->assertNotNull($slot1800);
        $this->assertEquals('outside_shift', $slot1800['status']);
        $this->assertEquals('outside_operating_hours', $slot1800['reason']);
        $this->assertEquals('Di Luar Jam Operasional', $slot1800['label']);
        $this->assertFalse($slot1800['is_available']);
    }

    /**
     * 10, 11, 12. Booking berhasil dibuat dengan status awal Pending.
     */
    public function test_booking_created_successfully_with_initial_pending_status(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'employee_id' => $this->employee1Branch1->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
            'notes' => 'Tolong tepat waktu',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($booking);
        $response->assertRedirect(route('customer.booking.show', $booking));

        $this->assertEquals(BookingStatus::Pending, $booking->status);
        $this->assertEquals($this->branch1->id, $booking->branch_id);
        $this->assertEquals($this->service30Min->id, $booking->service_id);
        $this->assertEquals($this->employee1Branch1->id, $booking->employee_id);
    }

    /**
     * 13. Validasi backend menolak manipulasi employee_id dari cabang lain.
     */
    public function test_backend_validation_rejects_employee_from_different_branch(): void
    {
        // Eko Prasetyo adalah karyawan Cabang 2, coba dibooking di Cabang 1
        $response = $this->actingAs($this->customer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'employee_id' => $this->employeeBranch2->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
        $response->assertSessionHasErrors(['employee_id']);

        $this->assertEquals(0, Booking::where('user_id', $this->customer->id)->count());
    }

    /**
     * Filter keahlian (skills): jika ada aturan keahlian yang cocok, hanya petugas berkualifikasi yang dimunculkan.
     */
    public function test_branch_employees_filtered_by_skills_when_rules_exist(): void
    {
        EmployeeSkill::create([
            'employee_id' => $this->employee2Branch1->id,
            'skill_name' => 'Cukur & Rapikan Jenggot',
        ]);

        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', [
                'branch_id' => $this->branch1->id,
                'service_id' => $this->service30Min->id,
            ])
        );

        $response->assertOk();
        $employeeIds = collect($response->json('employees'))->pluck('id')->all();

        // Hanya Gilang yang punya skill Cukur & Rapikan Jenggot
        $this->assertContains($this->employee2Branch1->id, $employeeIds);
        $this->assertNotContains($this->employee1Branch1->id, $employeeIds);
    }

    /**
     * Pelanggan mengganti tanggal booking ke tanggal baru di mana petugas memiliki shift.
     */
    public function test_changing_booking_date_reflects_employee_shift_availability(): void
    {
        $newDate = now()->addDays(5)->toDateString();

        // Di $newDate, Gilang memiliki shift
        EmployeeShift::create([
            'employee_id' => $this->employee2Branch1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $newDate,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Cek $this->testDate: Gilang libur
        $response1 = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'employee_id' => $this->employee2Branch1->id,
            ])
        );
        $this->assertTrue($response1->json('employee_off_duty'));

        // Cek $newDate: Gilang bertugas dan slot tersedia
        $response2 = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branch1->id,
                'date' => $newDate,
                'employee_id' => $this->employee2Branch1->id,
            ])
        );
        $this->assertFalse($response2->json('employee_off_duty'));
        $slot1000 = collect($response2->json('slots'))->firstWhere('time', '10:00');
        $this->assertTrue($slot1000['is_available']);
        $this->assertEquals('Tersedia', $slot1000['label']);
    }

    /**
     * Validasi backend menolak manipulasi service_id dari cabang lain.
     */
    public function test_backend_validation_rejects_service_from_different_branch(): void
    {
        // Service di Cabang 2
        $serviceBranch2 = Service::create([
            'service_category_id' => $this->service30Min->service_category_id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Cabang 2',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $serviceBranch2->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
        $response->assertSessionHasErrors(['service_id']);
    }
}
