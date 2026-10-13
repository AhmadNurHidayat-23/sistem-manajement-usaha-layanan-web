<?php

namespace Tests\Feature\Booking\Support;

use App\Enums\StatusShiftKaryawan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Modules\BookingAntrian\Services\BookingService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Base test case untuk fixture dan utilitas pengujian jadwal & shift booking.
 */
abstract class ShiftBookingTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected Branch $branch1;

    protected Branch $branch2;

    protected Employee $employee;

    protected Service $service60Min;

    protected Service $service30Min;

    protected BookingService $bookingService;

    protected string $testDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->bookingService = app(BookingService::class);
        $this->testDate = now()->addDays(5)->toDateString();

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        // Cabang 1: Buka 08:00 - 19:00, kapasitas 2
        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Jenderal Sudirman No. 10',
            'phone' => '081211112222',
            'opening_time' => '08:00',
            'closing_time' => '19:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        // Cabang 2: Buka 09:00 - 20:00, kapasitas 2
        $this->branch2 = Branch::create([
            'name' => 'Cabang Thamrin',
            'address' => 'Jl. MH Thamrin No. 20',
            'phone' => '081233334444',
            'opening_time' => '09:00',
            'closing_time' => '20:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Perawatan Kendaraan',
            'description' => 'Kategori layanan perawatan',
        ]);

        $this->service60Min = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci & Detailing Lengkap',
            'price' => 100000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->service30Min = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Kilat',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Ahmad Teknisi',
            'email' => 'ahmad@example.com',
            'role_id' => $employeeRole?->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $employeeUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Ahmad Teknisi',
            'phone' => '081299998888',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@example.com',
            'role_id' => $customerRole?->id,
            'password' => Hash::make('password123'),
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Pelanggan Lain',
            'email' => 'pelanggan2@example.com',
            'role_id' => $customerRole?->id,
            'password' => Hash::make('password123'),
        ]);

        // Default shift untuk employee di Cabang 1: 09:00 - 16:00
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $this->testDate,
            'start_time' => '09:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);
    }

    /**
     * Memperoleh bearer token autentikasi mobile untuk customer.
     */
    protected function getCustomerMobileToken(): string
    {
        $loginResponse = $this->postJson('/api/mobile/login', [
            'email' => $this->customer->email,
            'password' => 'password123',
        ]);

        return (string) $loginResponse->json('data.token');
    }
}
