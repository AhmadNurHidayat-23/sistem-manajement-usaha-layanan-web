<?php

namespace Tests\Feature\Dashboard;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisiDashboardKaryawanDanOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $employeeUser1;

    protected Employee $employee1;

    protected User $employeeUser2;

    protected Employee $employee2;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch = Branch::create([
            'name' => 'Cabang Tebet',
            'address' => 'Jl. Tebet No. 1',
            'phone' => '081211112222',
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Pak Owner',
            'email' => 'owner@simaul.com',
            'role_id' => $ownerRole->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Pak Manajer',
            'email' => 'manager@simaul.com',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $this->employeeUser1 = User::factory()->create([
            'name' => 'Budi Staf',
            'email' => 'budi@simaul.com',
            'role_id' => $employeeRole->id,
        ]);
        $this->employee1 = Employee::create([
            'user_id' => $this->employeeUser1->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Staf',
            'phone' => '081233334444',
            'status' => 'active',
        ]);

        $this->employeeUser2 = User::factory()->create([
            'name' => 'Joko Staf',
            'email' => 'joko@simaul.com',
            'role_id' => $employeeRole->id,
        ]);
        $this->employee2 = Employee::create([
            'user_id' => $this->employeeUser2->id,
            'branch_id' => $this->branch->id,
            'name' => 'Joko Staf',
            'phone' => '081255556666',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Andi Pelanggan',
            'email' => 'andi@example.com',
            'role_id' => $customerRole->id,
        ]);

        $category = ServiceCategory::create(['name' => 'Grooming']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Potong Rambut Signature',
            'price' => 75000,
            'estimated_duration' => 45,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);
    }

    /**
     * Revisi 1: Karyawan dapat memulai pengerjaan langsung dari tabel dashboard.
     */
    public function test_karyawan_dapat_memulai_pengerjaan_langsung_dari_tabel_dashboard(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Dashboard Karyawan menampilkan tombol Mulai Pengerjaan dan Detail
        $response = $this->actingAs($this->employeeUser1)->get(route('employee.dashboard'));
        $response->assertOk();
        $response->assertSee('Mulai Pengerjaan');
        $response->assertSee('Detail');

        // Klik Mulai Pengerjaan via PATCH
        $actionResponse = $this->actingAs($this->employeeUser1)
            ->from(route('employee.dashboard'))
            ->patch(route('bookings.update-status', $booking), [
                'status' => BookingStatus::InProgress->value,
            ]);

        $actionResponse->assertRedirect(route('employee.dashboard'));
        $actionResponse->assertSessionHas('success');
        $this->assertEquals(BookingStatus::InProgress, $booking->fresh()->status);

        // Setelah dimulai, tabel menampilkan tombol Selesaikan
        $dashboardUpdated = $this->actingAs($this->employeeUser1)->get(route('employee.dashboard'));
        $dashboardUpdated->assertOk();
        $dashboardUpdated->assertSee('Selesaikan');
        $dashboardUpdated->assertDontSee('Mulai Pengerjaan');
    }

    /**
     * Revisi 1: Karyawan dapat menyelesaikan pengerjaan langsung dari tabel dashboard.
     */
    public function test_karyawan_dapat_menyelesaikan_pengerjaan_langsung_dari_tabel_dashboard(): void
    {
        $booking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::InProgress,
        ]);

        $response = $this->actingAs($this->employeeUser1)->get(route('employee.dashboard'));
        $response->assertOk();
        $response->assertSee('Selesaikan');
        $response->assertSee('Detail');

        // Klik Selesaikan via PATCH
        $actionResponse = $this->actingAs($this->employeeUser1)
            ->from(route('employee.dashboard'))
            ->patch(route('bookings.update-status', $booking), [
                'status' => BookingStatus::Completed->value,
            ]);

        $actionResponse->assertRedirect(route('employee.dashboard'));
        $actionResponse->assertSessionHas('success');
        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);

        // Setelah selesai, tidak ada tombol Mulai Pengerjaan atau Selesaikan, hanya Detail
        $dashboardFinished = $this->actingAs($this->employeeUser1)->get(route('employee.dashboard'));
        $dashboardFinished->assertOk();
        $dashboardFinished->assertDontSee('Mulai Pengerjaan');
        $dashboardFinished->assertDontSee('Selesaikan');
        $dashboardFinished->assertSee('Detail');
    }

    /**
     * Revisi 1: Karyawan tidak dapat mengubah status pekerjaan milik karyawan lain.
     */
    public function test_karyawan_tidak_dapat_mengubah_pekerjaan_karyawan_lain(): void
    {
        $bookingOther = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee2->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '14:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Karyawan 1 mencoba mengubah status booking milik Karyawan 2
        $response = $this->actingAs($this->employeeUser1)
            ->from(route('employee.dashboard'))
            ->patch(route('bookings.update-status', $bookingOther), [
                'status' => BookingStatus::InProgress->value,
            ]);

        $response->assertRedirect(route('employee.dashboard'));
        $response->assertSessionHas('error');
        $this->assertEquals(BookingStatus::Confirmed, $bookingOther->fresh()->status);
    }

    /**
     * Revisi 1: Order drop-off juga dapat dimulai dan diselesaikan langsung dari tabel jika ada.
     */
    public function test_karyawan_dapat_memulai_dan_menyelesaikan_order_dropoff(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'order_type' => 'drop_off',
            'status' => StatusPesanan::Received,
        ]);

        $response = $this->actingAs($this->employeeUser1)->get(route('employee.dashboard'));
        $response->assertOk();
        $response->assertSee('Mulai Pengerjaan');

        $this->actingAs($this->employeeUser1)
            ->patch(route('orders.update-status', $order), [
                'status' => StatusPesanan::Processing->value,
            ]);

        $this->assertEquals(StatusPesanan::Processing, $order->fresh()->status);

        $this->actingAs($this->employeeUser1)
            ->patch(route('orders.update-status', $order), [
                'status' => StatusPesanan::Completed->value,
            ]);

        $this->assertEquals(StatusPesanan::Completed, $order->fresh()->status);
    }

    /**
     * Revisi Khusus Manajer: Tampilan Manajer pada /bookings tidak memiliki tombol Buat Booking dan tanpa kolom Aksi.
     */
    public function test_tampilan_manager_pada_halaman_bookings_tanpa_tombol_buat_dan_tanpa_kolom_aksi(): void
    {
        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->manager)->get(route('bookings.index'));
        $response->assertOk();

        // 1. Tidak ada tombol Buat Booking
        $response->assertDontSee('id="btn-buat-booking"', false);
        $response->assertDontSee('Buat Booking');

        // 2. Tidak ada header kolom Aksi maupun tombol Detail / Edit
        $response->assertDontSee('<th class="px-3 py-2.5 text-right">Aksi</th>', false);
        $response->assertDontSee('>Aksi<', false);
        $response->assertDontSee('Detail');
        $response->assertDontSee('Edit');

        // 3. Kolom yang harus tetap ada
        $response->assertSee('Tanggal & Jam');
        $response->assertSee('Pelanggan');
        $response->assertSee('Cabang');
        $response->assertSee('Layanan');
        $response->assertSee('Pegawai');
        $response->assertSee('Status');

        // 4. Filter, tombol pencarian, dan reset tetap ada
        $response->assertSee('Tanggal Booking');
        $response->assertSee('Cabang');
        $response->assertSee('Status Booking');
        $response->assertSee('<span>Cari</span>', false);
        $response->assertSee('title="Reset Filter"', false);
    }

    /**
     * Revisi Khusus Manajer: Sidebar Manajer pada /bookings tetap mengaktifkan menu Dashboard.
     */
    public function test_sidebar_manager_pada_halaman_bookings_tetap_mengaktifkan_menu_dashboard(): void
    {
        // Akses route bookings.index biasa
        $response = $this->actingAs($this->manager)->get(route('bookings.index'));
        $response->assertOk();

        // Menu Dashboard pada sidebar Manajer memiliki class aktif biru navy
        $response->assertSee('bg-[#062072] text-white font-semibold shadow-xs', false);

        // Akses via URL langsung dengan query string filter tetap mengaktifkan menu Dashboard
        $responseQuery = $this->actingAs($this->manager)->get('/bookings?status=confirmed');
        $responseQuery->assertOk();
        $responseQuery->assertSee('bg-[#062072] text-white font-semibold shadow-xs', false);

        // Tidak ada menu navigasi Booking Appointment baru yang ditambahkan pada sidebar
        $response->assertDontSee('sidebar-label truncate">Booking Appointment<', false);
    }

    /**
     * Revisi Khusus Manajer: Manajer hanya melihat data booking cabang tanggung jawabnya.
     */
    public function test_manager_hanya_melihat_data_booking_cabang_tanggung_jawabnya(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Bintaro',
            'address' => 'Jl. Bintaro Raya No. 10',
            'phone' => '081299998888',
            'opening_time' => '09:00',
            'closing_time' => '21:00',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $category = ServiceCategory::first();
        $otherService = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Hair Spa Bintaro Exclusive',
            'price' => 120000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $otherCustomer = User::factory()->create([
            'name' => 'Pelanggan Bintaro Unik',
            'email' => 'bintaro_customer@example.com',
            'role_id' => Role::where('name', UserRole::Customer->value)->value('id'),
        ]);

        // Booking di cabang Manajer (Tebet)
        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        // Booking di cabang lain (Bintaro)
        Booking::create([
            'user_id' => $otherCustomer->id,
            'branch_id' => $otherBranch->id,
            'service_id' => $otherService->id,
            'employee_id' => null,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '11:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->manager)->get(route('bookings.index'));
        $response->assertOk();

        // Melihat booking cabang tanggung jawabnya
        $response->assertSee($this->customer->name);
        $response->assertSee('Cabang Tebet');

        // Tidak melihat booking cabang lain
        $response->assertDontSee('Pelanggan Bintaro Unik');
        $response->assertDontSee('Hair Spa Bintaro Exclusive');
    }

    /**
     * Regresi Owner: Hak akses Owner dan tampilan Owner pada /bookings tetap memiliki tombol Buat Booking dan kolom Aksi.
     */
    public function test_regresi_owner_pada_halaman_bookings_dan_sidebar(): void
    {
        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'employee_id' => $this->employee1->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => '10:00',
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->actingAs($this->owner)->get(route('bookings.index'));
        $response->assertOk();

        // Owner tetap memiliki tombol Buat Booking dan tombol Detail
        $response->assertSee('id="btn-buat-booking"', false);
        $response->assertSee('Buat Booking');
        $response->assertSee('>Aksi<', false);
        $response->assertSee('Detail');

        // Sidebar Owner tidak mengaktifkan menu Dashboard saat berada di /bookings
        $response->assertDontSee('bg-[#062072] text-white font-semibold shadow-xs', false);

        // Namun saat Owner membuka dashboard-nya, menu Dashboard tetap aktif
        $ownerDashResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $ownerDashResponse->assertOk();
        $ownerDashResponse->assertSee('bg-[#062072] text-white font-semibold shadow-xs', false);
    }

    /**
     * Revisi 3: Alignment kolom Total Transaksi rata kiri dan nominal hijau pada dashboard Owner dan Manager.
     */
    public function test_alignment_kolom_total_transaksi_rata_kiri_pada_dashboard(): void
    {
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
        ]);

        Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $cashier->id,
            'transaction_code' => 'TRX-ALGN-001',
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        // Dashboard Owner
        $ownerResp = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $ownerResp->assertOk();
        $ownerResp->assertSee('<th class="py-2 px-3 text-left">Total Transaksi</th>', false);
        $ownerResp->assertSee('text-left font-semibold text-emerald-600 whitespace-nowrap', false);
        $ownerResp->assertSee('Rp 150.000', false);

        // Dashboard Manager
        $mgrResp = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $mgrResp->assertOk();
        $mgrResp->assertSee('<th class="py-2 px-3 text-left">Total Transaksi</th>', false);
        $mgrResp->assertSee('text-left font-bold text-emerald-600 whitespace-nowrap', false);
        $mgrResp->assertSee('Rp 150.000', false);
    }
}
