<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Http\Controllers\Karyawan\DetailTugasController;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KaryawanFinalisasiUxDanDetailUrlTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $otherEmployeeUser;

    protected Employee $otherEmployee;

    protected User $customerUser;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. Merdeka No. 10',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Perawatan Rambut']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Gentleman Haircut',
            'price' => 75000,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->employeeUser = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'status' => 'active',
        ]);

        $this->otherEmployeeUser = User::create([
            'name' => 'Rian Barber',
            'email' => 'rian.barber@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->otherEmployee = Employee::create([
            'user_id' => $this->otherEmployeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Rian Barber',
            'status' => 'active',
        ]);

        $this->customerUser = User::create([
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_queue_page_renders_unified_tasks_and_contextual_action_buttons(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $order = Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Processing,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('queue.index'));
        $response->assertOk();
        $response->assertSee('Antrean &amp; Tugas', false);
        $response->assertSee('Mulai Pengerjaan'); // Action for confirmed booking
        $response->assertSee('Tandai Selesai'); // Action for processing order
        $response->assertSee('Detail Tugas');
    }

    public function test_queue_status_filtering_works_correctly(): void
    {
        Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Confirmed,
        ]);

        Order::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Processing,
        ]);

        // Filter Menunggu
        $resMenunggu = $this->actingAs($this->employeeUser)->get(route('queue.index', ['status' => 'menunggu']));
        $resMenunggu->assertOk();
        $resMenunggu->assertSee('09:00');
        $resMenunggu->assertSee('Mulai Pengerjaan');
        $resMenunggu->assertDontSee('Tandai Selesai');

        // Filter Dikerjakan
        $resDikerjakan = $this->actingAs($this->employeeUser)->get(route('queue.index', ['status' => 'dikerjakan']));
        $resDikerjakan->assertOk();
        $resDikerjakan->assertSee('Tandai Selesai');
        $resDikerjakan->assertDontSee('Mulai Pengerjaan');
    }

    public function test_history_view_displays_past_completed_tasks(): void
    {
        Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::yesterday()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Completed,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('queue.index', ['view' => 'riwayat']));
        $response->assertOk();
        $response->assertSee('<title>Riwayat Tugas | SIMAUL</title>', false);
        $response->assertSee('Riwayat Tugas');
        $response->assertSee('Gentleman Haircut');
        $response->assertSee('Selesai');
    }

    public function test_detail_tugas_url_without_numeric_id_resolves_correctly(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $token = DetailTugasController::makeTaskToken('booking', $booking->id);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.tugas.detail', ['ref' => $token]));
        $response->assertOk();
        $response->assertSee('<title>Detail Tugas | SIMAUL</title>', false);
        $response->assertSee('Detail Tugas');
        $response->assertSee('Mulai Pengerjaan');
        $response->assertDontSee('Referensi #'.$booking->id);
    }

    public function test_detail_tugas_direct_url_without_ref_resolves_active_task(): void
    {
        Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.tugas.detail'));
        $response->assertOk();
        $response->assertSee('Detail Tugas');
        $response->assertSee('Mulai Pengerjaan');
    }

    public function test_detail_tugas_rejects_other_employees_task_token(): void
    {
        $otherBooking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->otherEmployee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $token = DetailTugasController::makeTaskToken('booking', $otherBooking->id);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.tugas.detail', ['ref' => $token]));
        // Must redirect back with notice because booking belongs to other employee
        $response->assertRedirect(route('queue.index'));
    }

    public function test_pending_booking_cannot_be_started_by_employee(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('bookings.show', $booking));
        $response->assertOk();
        $response->assertDontSee('Mulai Pengerjaan');
        $response->assertSee('Menunggu Konfirmasi');
    }
}
