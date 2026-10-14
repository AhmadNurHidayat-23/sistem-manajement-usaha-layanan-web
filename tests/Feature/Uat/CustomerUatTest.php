<?php

namespace Tests\Feature\Uat;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Service;
use App\Models\Ulasan;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerUatTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $employee;

    protected Branch $branch1;

    protected Service $service1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);

        $this->customer = User::whereIn('email', ['pelanggan@simaul.com', 'customer@example.com'])->firstOrFail();
        $this->employee = User::whereIn('email', ['karyawan@simaul.com', 'employee@example.com'])->firstOrFail();
        $this->branch1 = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $this->service1 = Service::where('branch_id', $this->branch1->id)->firstOrFail();
    }

    public function test_uat_customer_login_and_logout(): void
    {
        $response = $this->post('/login', [
            'email' => 'pelanggan@simaul.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($this->customer);

        $logout = $this->post('/logout');
        $logout->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_uat_customer_public_catalog(): void
    {
        $response = $this->actingAs($this->customer)->get('/catalog');
        $response->assertStatus(200);
        $response->assertSee('Katalog');
        $response->assertSee($this->service1->name);
    }

    public function test_uat_customer_booking_and_pesanan(): void
    {
        $response = $this->actingAs($this->customer)->get(route('bookings.index'));
        $response->assertStatus(200);
        $response->assertSee('Booking');

        $emp = Employee::where('branch_id', $this->branch1->id)->first();
        if ($emp) {
            EmployeeShift::firstOrCreate(
                [
                    'employee_id' => $emp->id,
                    'shift_date' => now()->addDays(2)->toDateString(),
                ],
                [
                    'branch_id' => $this->branch1->id,
                    'start_time' => '08:00',
                    'end_time' => '18:00',
                    'status' => StatusShiftKaryawan::Scheduled,
                ]
            );
        }

        $createBooking = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service1->id,
            'employee_id' => $emp?->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '10:00',
            'notes' => 'UAT Booking Test',
        ]);
        $createBooking->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->customer->id,
            'notes' => 'UAT Booking Test',
        ]);

        $ordersResponse = $this->actingAs($this->customer)->get(route('orders.index'));
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Order');

        $dropOffBranch = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $dropOffService = Service::where('branch_id', $dropOffBranch->id)->firstOrFail();

        $createOrder = $this->actingAs($this->customer)->post(route('orders.store'), [
            'branch_id' => $dropOffBranch->id,
            'service_id' => $dropOffService->id,
            'estimated_quantity' => 1,
            'pickup_option' => 1,
            'notes' => 'UAT Drop-off Test',
        ]);
        $createOrder->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'notes' => 'UAT Drop-off Test',
        ]);
    }

    public function test_uat_customer_member_points_promo_and_ulasan(): void
    {
        $memberResponse = $this->actingAs($this->customer)->get(route('members.my-membership'));
        $memberResponse->assertStatus(200);
        $memberResponse->assertSee('MBR-DEMO01');
        $memberResponse->assertSee('75');

        $packagesResponse = $this->actingAs($this->customer)->get(route('members.packages'));
        $packagesResponse->assertStatus(200);

        $promoResponse = $this->actingAs($this->customer)->get(route('promotions.public'));
        $promoResponse->assertStatus(200);
        $promoResponse->assertSee('DISKON10');

        $ulasan = Ulasan::where('user_id', $this->customer->id)->firstOrFail();
        $editResponse = $this->actingAs($this->customer)->get(route('ulasan.edit', $ulasan));
        $editResponse->assertStatus(200);

        $updateResponse = $this->actingAs($this->customer)->put(route('ulasan.update', $ulasan), [
            'rating' => 4,
            'komentar' => 'Ulasan diubah saat UAT customer.',
        ]);
        $updateResponse->assertRedirect(route('bookings.index'));
        $this->assertEquals(4, $ulasan->fresh()->rating);
    }

    public function test_uat_karyawan_login_and_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => $this->employee->email,
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('employee.dashboard'));

        $dashboard = $this->actingAs($this->employee)->get(route('employee.dashboard'));
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Dashboard Karyawan');
    }

    public function test_uat_karyawan_antrian_and_shifts_and_commissions(): void
    {
        $queueResponse = $this->actingAs($this->employee)->get(route('queue.index'));
        $queueResponse->assertStatus(200);
        $queueResponse->assertSee('Antrian');

        $shiftsResponse = $this->actingAs($this->employee)->get(route('employees.my-shifts'));
        $shiftsResponse->assertStatus(200);
        $shiftsResponse->assertSee('Jadwal Shift');

        $commissionsResponse = $this->actingAs($this->employee)->get(route('employees.my-commissions'));
        $commissionsResponse->assertStatus(200);
        $commissionsResponse->assertSee('Komisi');

        $booking = Booking::where('employee_id', $this->employee->employee->id)
            ->where('status', BookingStatus::InProgress)
            ->first();

        if ($booking) {
            $updateStatus = $this->actingAs($this->employee)->patch(route('bookings.update-status', $booking), [
                'status' => BookingStatus::Completed->value,
            ]);
            $updateStatus->assertSessionHas('success');
            $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
        }
    }
}
