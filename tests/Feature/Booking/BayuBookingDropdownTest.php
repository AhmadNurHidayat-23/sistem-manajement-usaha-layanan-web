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
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BayuBookingDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branchBarbershop;

    protected Branch $branchLaundry;

    protected Employee $bayu;

    protected Employee $gilang;

    protected Employee $dedi;

    protected Employee $agusLaundry;

    protected Service $servicePotongPria;

    protected Service $servicePotongStyling;

    protected Service $serviceCukurJenggot;

    protected Service $servicePewarnaan;

    protected string $shiftDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->shiftDate = Carbon::tomorrow()->toDateString();

        // 1. Cabang 1: Barbershop & Studio (09:00 - 21:00)
        $this->branchBarbershop = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Thamrin No. 10',
            'phone' => '021-3901122',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'appointment',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        // 2. Cabang 2: Laundry & Shoe Care
        $this->branchLaundry = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. Panglima Polim No. 45',
            'phone' => '021-7203344',
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'service_mode' => 'both',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        // 3. Services di Barbershop
        $catBarber = ServiceCategory::create(['name' => 'Potong Rambut & Styling']);
        $catGrooming = ServiceCategory::create(['name' => 'Grooming & Cukur']);
        $catColoring = ServiceCategory::create(['name' => 'Pewarnaan Rambut']);

        $this->servicePotongPria = Service::create([
            'service_category_id' => $catBarber->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Potong Rambut Pria',
            'price' => 95000,
            'estimated_duration' => 45,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->servicePotongStyling = Service::create([
            'service_category_id' => $catBarber->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Potong Rambut & Styling',
            'price' => 110000,
            'estimated_duration' => 50,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->serviceCukurJenggot = Service::create([
            'service_category_id' => $catGrooming->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Cukur & Rapikan Jenggot',
            'price' => 65000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->servicePewarnaan = Service::create([
            'service_category_id' => $catColoring->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Pewarnaan Rambut',
            'price' => 180000,
            'estimated_duration' => 90,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        // 4. Karyawan Barbershop
        $userBayu = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->bayu = Employee::create([
            'user_id' => $userBayu->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Bayu Pratama',
            'phone' => '08110000101',
            'status' => 'active',
        ]);
        EmployeeSkill::create(['employee_id' => $this->bayu->id, 'skill_name' => 'Executive Haircut']);
        EmployeeSkill::create(['employee_id' => $this->bayu->id, 'skill_name' => 'Potong Rambut & Styling']);

        $userDedi = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->dedi = Employee::create([
            'user_id' => $userDedi->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Dedi Kurniawan',
            'phone' => '08110000100',
            'status' => 'active',
        ]);
        EmployeeSkill::create(['employee_id' => $this->dedi->id, 'skill_name' => 'Executive Haircut']);
        EmployeeSkill::create(['employee_id' => $this->dedi->id, 'skill_name' => 'Beard Styling & Shaving']);

        $userGilang = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->gilang = Employee::create([
            'user_id' => $userGilang->id,
            'branch_id' => $this->branchBarbershop->id,
            'name' => 'Gilang Ramadhan',
            'phone' => '08110000103',
            'status' => 'active',
        ]);
        EmployeeSkill::create(['employee_id' => $this->gilang->id, 'skill_name' => 'Cukur & Rapikan Jenggot']);
        EmployeeSkill::create(['employee_id' => $this->gilang->id, 'skill_name' => 'Paket Grooming Lengkap']);

        // 5. Karyawan Cabang Lain (Laundry)
        $userAgus = User::factory()->create(['role_id' => $employeeRole?->id]);
        $this->agusLaundry = Employee::create([
            'user_id' => $userAgus->id,
            'branch_id' => $this->branchLaundry->id,
            'name' => 'Agus Santoso',
            'phone' => '08110000201',
            'status' => 'active',
        ]);

        // 6. Shift Bayu Pratama: 13 Nov 2026, 08:00 - 16:00, Status Hadir
        EmployeeShift::create([
            'employee_id' => $this->bayu->id,
            'branch_id' => $this->branchBarbershop->id,
            'shift_date' => $this->shiftDate,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Present,
        ]);

        // 7. Customer
        $this->customer = User::factory()->create(['role_id' => $customerRole?->id]);
    }

    /**
     * Uji 1: Dropdown tanpa memilih layanan menampilkan seluruh karyawan sah cabang terpilih.
     * Bayu Pratama dan Gilang Ramadhan muncul, sedangkan karyawan cabang lain (Agus Laundry) tidak muncul.
     */
    public function test_dropdown_petugas_shows_all_branch_employees_when_no_service_selected(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', ['branch_id' => $this->branchBarbershop->id])
        );

        $response->assertOk();
        $employeeNames = collect($response->json('employees'))->pluck('name')->all();

        $this->assertContains('Bayu Pratama', $employeeNames);
        $this->assertContains('Gilang Ramadhan', $employeeNames);
        $this->assertContains('Dedi Kurniawan', $employeeNames);
        $this->assertNotContains('Agus Santoso', $employeeNames);
    }

    /**
     * Uji 2: Layanan Potong Rambut Pria memunculkan SEMUA karyawan cabang (Bayu, Gilang, Dedi),
     * tanpa memfilter berdasarkan skill.
     */
    public function test_all_branch_employees_appear_for_potong_rambut_pria(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', [
                'branch_id' => $this->branchBarbershop->id,
                'service_id' => $this->servicePotongPria->id,
            ])
        );

        $response->assertOk();
        $employeeNames = collect($response->json('employees'))->pluck('name')->all();

        $this->assertContains('Bayu Pratama', $employeeNames);
        $this->assertContains('Dedi Kurniawan', $employeeNames);
        $this->assertContains('Gilang Ramadhan', $employeeNames);
        $this->assertNotContains('Agus Santoso', $employeeNames);
    }

    /**
     * Uji 3: Layanan Cukur & Rapikan Jenggot tetap memunculkan Bayu Pratama, Gilang Ramadhan, dan Dedi.
     * Pemilihan layanan tidak boleh menghilangkan karyawan cabang terpilih dari dropdown.
     */
    public function test_all_branch_employees_appear_for_cukur_rapikan_jenggot(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', [
                'branch_id' => $this->branchBarbershop->id,
                'service_id' => $this->serviceCukurJenggot->id,
            ])
        );

        $response->assertOk();
        $employeeNames = collect($response->json('employees'))->pluck('name')->all();

        $this->assertContains('Bayu Pratama', $employeeNames);
        $this->assertContains('Gilang Ramadhan', $employeeNames);
        $this->assertContains('Dedi Kurniawan', $employeeNames);
        $this->assertNotContains('Agus Santoso', $employeeNames);
    }

    /**
     * Uji 4: Mengganti layanan (misal ke Pewarnaan Rambut) tidak mengurangi daftar petugas cabang.
     */
    public function test_changing_service_does_not_reduce_or_alter_branch_employee_list(): void
    {
        $resNoService = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', ['branch_id' => $this->branchBarbershop->id])
        );
        $resColoring = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', [
                'branch_id' => $this->branchBarbershop->id,
                'service_id' => $this->servicePewarnaan->id,
            ])
        );

        $listNoService = collect($resNoService->json('employees'))->pluck('id')->sort()->values()->all();
        $listColoring = collect($resColoring->json('employees'))->pluck('id')->sort()->values()->all();

        $this->assertEquals($listNoService, $listColoring);
        $this->assertContains($this->bayu->id, $listColoring);
        $this->assertContains($this->gilang->id, $listColoring);
        $this->assertContains($this->dedi->id, $listColoring);
    }

    /**
     * Uji 5: Status Hadir atau Terjadwal pada shift tidak membuat petugas hilang dari dropdown.
     */
    public function test_shift_presence_does_not_exclude_employee_from_dropdown(): void
    {
        // Bayu memiliki shift status 'present', Gilang tidak memiliki shift pada $this->shiftDate
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.branch-data', [
                'branch_id' => $this->branchBarbershop->id,
                'service_id' => $this->servicePotongPria->id,
            ])
        );

        $response->assertOk();
        $employeeIds = collect($response->json('employees'))->pluck('id')->all();
        $this->assertContains($this->bayu->id, $employeeIds);
    }

    /**
     * Uji 6: Ketersediaan slot waktu untuk Bayu Pratama pada tanggal shift 13 November 2026:
     * - Jam shift: 08:00 - 16:00
     * - Jam buka cabang: 09:00 - 21:00
     * - Slot jam 10:00: Tersedia
     * - Slot jam 16:30 atau 17:00 (di luar shift Bayu): Petugas Libur (is_available = false)
     */
    public function test_bayu_pratama_slot_availability_matches_shift_hours(): void
    {
        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branchBarbershop->id,
                'date' => $this->shiftDate,
                'employee_id' => $this->bayu->id,
                'service_id' => $this->servicePotongPria->id,
            ])
        );

        $response->assertOk();
        $response->assertJson(['is_open' => true, 'employee_off_duty' => false]);

        $slots = $response->json('slots');

        // Jam 10:00 berada di dalam jam shift (08:00 - 16:00) dan jam cabang (09:00 - 21:00)
        $slot1000 = collect($slots)->firstWhere('time', '10:00');
        $this->assertNotNull($slot1000);
        $this->assertTrue($slot1000['is_available']);
        $this->assertEquals('available', $slot1000['status']);
        $this->assertEquals('Tersedia', $slot1000['label']);

        // Jam 17:00 berada di luar jam shift Bayu (shift selesai 16:00) -> outside_shift
        $slot1700 = collect($slots)->firstWhere('time', '17:00');
        $this->assertNotNull($slot1700);
        $this->assertFalse($slot1700['is_available']);
        $this->assertEquals('outside_shift', $slot1700['status']);
        $this->assertEquals('Petugas Tidak Tersedia', $slot1700['label']);
    }

    /**
     * Uji 7: Booking bentrok dicegah ketika Bayu Pratama sudah memiliki booking pada jam tertentu.
     */
    public function test_booking_conflict_is_prevented_for_bayu_pratama(): void
    {
        // Buat booking jam 10:00 untuk Bayu
        Booking::create([
            'branch_id' => $this->branchBarbershop->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->servicePotongPria->id,
            'employee_id' => $this->bayu->id,
            'booking_date' => $this->shiftDate,
            'booking_time' => '10:00:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->customer)->getJson(
            route('customer.booking.available-slots', [
                'branch_id' => $this->branchBarbershop->id,
                'date' => $this->shiftDate,
                'employee_id' => $this->bayu->id,
                'service_id' => $this->servicePotongPria->id,
            ])
        );

        $response->assertOk();
        $slots = $response->json('slots');

        // Slot 10:00 harus menjadi 'full' / 'Petugas Tidak Tersedia'
        $slot1000 = collect($slots)->firstWhere('time', '10:00');
        $this->assertNotNull($slot1000);
        $this->assertFalse($slot1000['is_available']);
        $this->assertEquals('full', $slot1000['status']);
        $this->assertEquals('Petugas Tidak Tersedia', $slot1000['label']);

        // Slot 11:00 tetap tersedia
        $slot1100 = collect($slots)->firstWhere('time', '11:00');
        $this->assertNotNull($slot1100);
        $this->assertTrue($slot1100['is_available']);
        $this->assertEquals('Tersedia', $slot1100['label']);
    }

    /**
     * Uji 8: Karyawan dari cabang lain tidak dapat dibooking di cabang ini.
     */
    public function test_employee_from_another_branch_cannot_be_booked(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branchBarbershop->id,
            'service_id' => $this->servicePotongPria->id,
            'employee_id' => $this->agusLaundry->id,
            'booking_date' => $this->shiftDate,
            'booking_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['employee_id']);
        $this->assertEquals(0, Booking::count());
    }

    /**
     * Uji 9: Dropdown petugas memiliki placeholder Pilih Petugas dengan atribut data-placeholder dan data-clearable.
     */
    public function test_petugas_dropdown_structure_and_placeholder(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.booking.create', [
            'branch_id' => $this->branchBarbershop->id,
        ]));

        $response->assertOk();
        $response->assertSee('Petugas <span class="text-xs text-[#667085] font-normal">(opsional)</span>', false);
        $response->assertSee('data-clearable="true"', false);
        $response->assertSee('<option value="" disabled selected hidden data-placeholder="true">Pilih Petugas</option>', false);
        $response->assertSee('Bayu Pratama');
        $response->assertSee('Gilang Ramadhan');
        $response->assertSee('Dedi Kurniawan');
    }
}
