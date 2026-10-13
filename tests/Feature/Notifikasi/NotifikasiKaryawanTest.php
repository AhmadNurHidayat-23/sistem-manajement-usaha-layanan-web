<?php

namespace Tests\Feature\Notifikasi;

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
use App\Modules\BookingAntrian\Services\StatusPekerjaanService;
use Database\Seeders\PeranSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiKaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $managerUser;

    protected Employee $employeeA;

    protected Employee $employeeB;

    protected User $customer;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Barbershop Test',
            'address' => 'Jl. Merdeka 10',
            'is_active' => true,
            'service_mode' => 'both',
        ]);

        $cat = ServiceCategory::create(['name' => 'Haircut']);
        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $cat->id,
            'name' => 'Potong Rambut Pria',
            'price' => 50000,
            'is_active' => true,
            'service_type' => 'both',
        ]);

        $empRole = Role::where('name', UserRole::Employee->value)->first();
        $mgrRole = Role::where('name', UserRole::Manager->value)->first();
        $custRole = Role::where('name', UserRole::Customer->value)->first();

        $userA = User::factory()->create(['role_id' => $empRole->id, 'name' => 'Dedi Kurniawan']);
        $this->employeeA = Employee::create([
            'user_id' => $userA->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dedi Kurniawan',
            'status' => 'active',
        ]);

        $userB = User::factory()->create(['role_id' => $empRole->id, 'name' => 'Budi Santoso']);
        $this->employeeB = Employee::create([
            'user_id' => $userB->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);

        $this->managerUser = User::factory()->create(['role_id' => $mgrRole->id, 'name' => 'Manajer Cabang']);
        $this->managerUser->branches()->attach($this->branch->id);

        $this->customer = User::factory()->create(['role_id' => $custRole->id, 'name' => 'Rudi Pelanggan']);
    }

    public function test_appointment_baru_menghasilkan_notifikasi_hanya_ke_petugas_yang_ditugaskan(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeA->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Pending,
        ]);

        $userANotifs = $this->employeeA->user->notifications;
        $this->assertCount(1, $userANotifs);
        $this->assertSame('Tugas Baru Ditugaskan', $userANotifs->first()->data['title']);
        $this->assertStringContainsString('Potong Rambut Pria', $userANotifs->first()->data['message']);

        // Karyawan B di cabang yang sama tidak boleh menerima notifikasi tugas A
        $this->assertCount(0, $this->employeeB->user->notifications);
    }

    public function test_drop_off_ditugaskan_menghasilkan_notifikasi_ke_karyawan(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeA->id,
            'status' => StatusPesanan::Received,
        ]);

        $notifs = $this->employeeA->user->notifications;
        $this->assertCount(1, $notifs);
        $this->assertSame('Tugas Baru Ditugaskan', $notifs->first()->data['title']);
    }

    public function test_drop_off_tanpa_petugas_tidak_dikirim_ke_semua_karyawan_tetapi_ke_manajer(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => null,
            'status' => StatusPesanan::Pending,
        ]);

        $this->assertCount(0, $this->employeeA->user->notifications);
        $this->assertCount(0, $this->employeeB->user->notifications);

        $mgrNotifs = $this->managerUser->notifications;
        $this->assertCount(1, $mgrNotifs);
        $this->assertSame('Pesanan Memerlukan Penugasan', $mgrNotifs->first()->data['title']);
    }

    public function test_reassignment_memberitahu_petugas_baru_dan_petugas_lama(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeA->id,
            'status' => StatusPesanan::Received,
        ]);

        // Dialihkan ke employee B oleh manager
        $this->actingAs($this->managerUser)
            ->patch(route('orders.update-status', $order), [
                'status' => 'received',
                'employee_id' => $this->employeeB->id,
            ])
            ->assertRedirect();

        $notifOld = $this->employeeA->user->notifications()->get()->last();
        $this->assertSame('Pengalihan Tugas', $notifOld->data['title']);

        $notifNew = $this->employeeB->user->notifications()->get()->last();
        $this->assertSame('Tugas Baru Ditugaskan', $notifNew->data['title']);
    }

    public function test_petugas_lama_tidak_dapat_mengerjakan_tugas_setelah_dialihkan(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeB->id,
            'status' => StatusPesanan::Received,
        ]);

        // Employee A mencoba mengakses tugas Employee B
        $this->actingAs($this->employeeA->user)
            ->get(route('employee.tugas.show', ['identifier' => $order->uuid]))
            ->assertForbidden();

        // Employee A mencoba mengubah status tugas Employee B
        $statusService = app(StatusPekerjaanService::class);
        $this->expectException(AuthorizationException::class);
        $statusService->updateOrderStatus($this->employeeA->user, $order, 'processing');
    }

    public function test_pembatalan_dan_perubahan_jadwal_memberi_notifikasi_ke_karyawan(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeA->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Perubahan jadwal
        $booking->update(['booking_time' => '14:00']);
        $latestNotif = $this->employeeA->user->notifications()->get()->last();
        $this->assertSame('Perubahan Jadwal Tugas', $latestNotif->data['title']);

        // Pembatalan
        $booking->update(['status' => BookingStatus::Cancelled]);
        $cancelNotif = $this->employeeA->user->notifications()->get()->last();
        $this->assertSame('Tugas Dibatalkan', $cancelNotif->data['title']);
    }

    public function test_mulai_pengerjaan_dan_tandai_selesai_tetap_berfungsi(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employeeA->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $statusService = app(StatusPekerjaanService::class);

        // Mulai pengerjaan
        $statusService->updateBookingStatus($this->employeeA->user, $booking, 'in_progress');
        $this->assertSame(BookingStatus::InProgress, $booking->fresh()->status);

        // Tandai selesai
        $statusService->updateBookingStatus($this->employeeA->user, $booking, 'completed');
        $this->assertSame(BookingStatus::Completed, $booking->fresh()->status);
    }
}
