<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Http\Controllers\Karyawan\DetailTugasController;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\Karyawan\ShiftKaryawanService;
use Carbon\Carbon;
use Database\Seeders\DemoEmployeeShiftSeeder;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalisasiHeaderSidebarShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $managerUser;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Merdeka No. 10',
            'opening_time' => '09:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $this->managerUser = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Manager Toko',
            'email' => 'manager@simaul.com',
        ]);
        $this->managerUser->branches()->attach($this->branch->id);
    }

    public function test_employee_header_has_realtime_clock_search_and_profile_with_name(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));

        $response->assertOk();
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="realtime-clock-time"', false);
        $response->assertSee('id="realtime-clock-date"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('Dedi Kurniawan');
        $response->assertDontSee('KARYAWAN');
    }

    public function test_employee_sidebar_uses_white_background_and_exact_logo_asset(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));

        $response->assertOk();
        $response->assertSee('images/brand/logo-header.png');
        $response->assertSee('bg-white text-[#101828]', false);
        $response->assertSee('Utama');
        $response->assertSee('Operasional');
        $response->assertSee('Kinerja');
        $response->assertSee('Dashboard');
        $response->assertSee('Antrean & Tugas', false);
        $response->assertSee('Jadwal Shift');
        $response->assertSee('Komisi Saya');
        $response->assertSee('Keluar');
        $response->assertSee('Dedi Kurniawan');
    }

    public function test_manager_header_keeps_name_and_clock_without_role_badge(): void
    {
        $response = $this->actingAs($this->managerUser)->get(route('manager.dashboard'));

        $response->assertOk();
        $response->assertSee('id="header-realtime-clock"', false);
        $response->assertSee('id="menu-search-input"', false);
        $response->assertSee('Manager Toko');
        $response->assertDontSee('MANAJER');
    }

    public function test_employee_can_only_see_their_own_shifts(): void
    {
        $otherUser = User::factory()->create([
            'role_id' => Role::where('name', UserRole::Employee->value)->first()->id,
            'name' => 'Agus Santoso',
            'email' => 'karyawan.laundry@simaul.com',
        ]);
        $otherEmp = Employee::create([
            'user_id' => $otherUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'phone' => '081234567899',
            'status' => 'active',
        ]);

        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => '2026-10-13',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        EmployeeShift::create([
            'employee_id' => $otherEmp->id,
            'branch_id' => $this->branch->id,
            'shift_date' => '2026-10-13',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employees.my-shifts'));

        $response->assertOk();
        $response->assertSee('Dedi Kurniawan');
        $response->assertDontSee('Agus Santoso');
    }

    public function test_shifts_exceeding_8_hours_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Durasi satu shift kerja tidak boleh melebihi 8 jam.');

        app(ShiftKaryawanService::class)->createShift($this->managerUser, [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_date' => '2026-10-14',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00', // 9 jam -> ditolak
            'status' => StatusShiftKaryawan::Scheduled->value,
        ]);
    }

    public function test_demo_employee_shift_seeder_is_idempotent(): void
    {
        $this->seed(DemoEmployeeShiftSeeder::class);
        $countAfterFirst = EmployeeShift::where('employee_id', $this->employee->id)->count();
        $this->assertGreaterThan(0, $countAfterFirst);

        // Seed kedua kali tidak membuat duplikasi
        $this->seed(DemoEmployeeShiftSeeder::class);
        $countAfterSecond = EmployeeShift::where('employee_id', $this->employee->id)->count();
        $this->assertEquals($countAfterFirst, $countAfterSecond);

        // Semua shift karyawan <= 8 jam
        $shifts = EmployeeShift::where('employee_id', $this->employee->id)->get();
        foreach ($shifts as $s) {
            $start = Carbon::parse($s->start_time);
            $end = Carbon::parse($s->end_time);
            $this->assertLessThanOrEqual(480, $start->diffInMinutes($end));
        }
    }

    public function test_sidebar_antrean_menu_active_on_queue_details_and_history(): void
    {
        $cat = ServiceCategory::create(['name' => 'Haircut']);
        $service = Service::create([
            'service_category_id' => $cat->id,
            'branch_id' => $this->branch->id,
            'name' => 'Basic Cut',
            'price' => 50000,
            'service_type' => 'both',
            'is_active' => true,
        ]);
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create(['role_id' => $customerRole->id]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00:00',
            'status' => BookingStatus::InProgress,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Processing,
        ]);

        // 1. /queue -> Antrean & Tugas aktif
        $resQueue = $this->actingAs($this->employeeUser)->get(route('queue.index'));
        $resQueue->assertOk();
        $this->assertStringContainsString('data-alias="Antrian"', $resQueue->getContent());
        $this->assertStringContainsString('bg-[#062072]', $resQueue->getContent());

        // 2. /queue?view=riwayat -> Antrean & Tugas aktif
        $resRiwayat = $this->actingAs($this->employeeUser)->get(route('queue.index', ['view' => 'riwayat']));
        $resRiwayat->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resRiwayat->getContent());

        // 3. Detail Appointment /bookings/{id} -> Antrean & Tugas aktif
        $resBooking = $this->actingAs($this->employeeUser)->get(route('bookings.show', $booking));
        $resBooking->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resBooking->getContent());

        // 4. Detail Drop-off /orders/{id} -> Antrean & Tugas aktif
        $resOrder = $this->actingAs($this->employeeUser)->get(route('orders.show', $order));
        $resOrder->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resOrder->getContent());

        // 5. Token detail /employee/tugas/detail -> Antrean & Tugas aktif
        $token = DetailTugasController::makeTaskToken('booking', $booking->id);
        $resToken = $this->actingAs($this->employeeUser)->get(route('employee.tugas.detail', ['ref' => $token]));
        $resToken->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resToken->getContent());

        // 6. Refresh / Direct visit tetap aktif
        $resRefresh = $this->actingAs($this->employeeUser)->get(route('bookings.show', $booking));
        $resRefresh->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resRefresh->getContent());
    }

    public function test_sidebar_other_menus_active_state_isolation(): void
    {
        // Dashboard -> Dashboard aktif, Antrean tidak aktif
        $resDash = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $resDash->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/dashboard"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/dashboard"/', $resDash->getContent());
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resDash->getContent());

        // Shift -> Jadwal Shift aktif, Antrean tidak aktif
        $resShift = $this->actingAs($this->employeeUser)->get(route('employees.my-shifts'));
        $resShift->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/my-shifts"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/my-shifts"/', $resShift->getContent());
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resShift->getContent());

        // Komisi -> Komisi Saya aktif, Antrean tidak aktif
        $resComm = $this->actingAs($this->employeeUser)->get(route('employees.my-commissions'));
        $resComm->assertOk();
        $this->assertMatchesRegularExpression('/href="[^"]*\/my-commissions"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/my-commissions"/', $resComm->getContent());
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*\/queue"[^>]*bg-\[#062072\]|bg-\[#062072\][^>]*href="[^"]*\/queue"/', $resComm->getContent());
    }
}
