<?php

namespace Tests\Feature\Karyawan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\UserRole;
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

class RiwayatTugasKaryawanSelesaiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected User $otherEmployeeUser;

    protected Employee $otherEmployee;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)',
            'address' => 'Jl. RS Fatmawati No. 45',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Laundry Kiloan']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Kering Setrika',
            'price' => 12000,
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $this->employeeUser = User::create([
            'name' => 'Agus Santoso',
            'email' => 'karyawan.laundry@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'status' => 'active',
        ]);

        $this->otherEmployeeUser = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'karyawan@simaul.com',
            'password' => Hash::make('password123'),
            'role_id' => $employeeRole->id,
        ]);
        $this->otherEmployee = Employee::create([
            'user_id' => $this->otherEmployeeUser->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'status' => 'active',
        ]);

        $this->customer = User::create([
            'name' => 'Rian Saputra',
            'email' => 'rian@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_completed_orders_and_bookings_appear_in_history_tasks_on_the_same_day(): void
    {
        // 1. Order selesai hari ini
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Completed,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Booking selesai hari ini
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Completed,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $response->assertOk();

        $historyTasks = $response->viewData('historyTasks');
        $todayTasks = $response->viewData('todayTasks');

        // Harus masuk ke riwayat tugas
        $this->assertCount(2, $historyTasks);
        $historyIds = $historyTasks->pluck('id')->all();
        $this->assertContains($order->id, $historyIds);
        $this->assertContains($booking->id, $historyIds);

        // Tidak boleh muncul di tugas aktif hari ini
        $this->assertCount(0, $todayTasks);
    }

    public function test_active_tasks_appear_in_today_tasks_and_not_in_history(): void
    {
        $activeOrder = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Processing,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $response->assertOk();

        $historyTasks = $response->viewData('historyTasks');
        $todayTasks = $response->viewData('todayTasks');

        $this->assertCount(0, $historyTasks);
        $this->assertCount(1, $todayTasks);
        $this->assertEquals($activeOrder->id, $todayTasks->first()['id']);
    }

    public function test_cancelled_orders_do_not_appear_in_history_tasks(): void
    {
        Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Cancelled,
            'created_at' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $response->assertOk();

        $historyTasks = $response->viewData('historyTasks');
        $this->assertCount(0, $historyTasks);
    }

    public function test_history_tasks_only_displays_tasks_assigned_to_the_logged_in_employee(): void
    {
        // Tugas selesai untuk karyawan lain
        Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->otherEmployee->id,
            'status' => StatusPesanan::Completed,
            'created_at' => Carbon::now(),
        ]);

        // Tugas selesai untuk karyawan ini
        $myOrder = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Completed,
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $response->assertOk();

        $historyTasks = $response->viewData('historyTasks');
        $this->assertCount(1, $historyTasks);
        $this->assertEquals($myOrder->id, $historyTasks->first()['id']);
    }

    public function test_employee_dashboard_renders_history_tasks_with_customer_and_branch_info(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee->id,
            'status' => StatusPesanan::Completed,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('employee.dashboard'));
        $response->assertOk();

        $response->assertSee('Riwayat Tugas');
        $response->assertSee('Cuci Kering Setrika');
        $response->assertSee('Rian Saputra');
        $response->assertSee(route('orders.show', $order));
        $response->assertSee('Detail');
    }
}
