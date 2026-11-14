<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\EmployeeShift;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\BookingAntrian\AntrianLayananService;
use App\Services\BookingAntrian\StatusPekerjaanService;
use App\Services\Karyawan\KomisiKaryawanService;
use Tests\Feature\Booking\Support\ShiftBookingTestCase;

class AlurJanjiTemuOtomatisTest extends ShiftBookingTestCase
{
    protected User $cashierUser;

    protected User $managerUser;

    protected Employee $otherEmployeeBranch1;

    protected Employee $employeeBranch2;

    protected function setUp(): void
    {
        parent::setUp();

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        // Kasir Cabang 1
        $this->cashierUser = User::factory()->create([
            'name' => 'Kasir Cabang 1',
            'email' => 'kasir1@example.com',
            'role_id' => $cashierRole?->id,
        ]);
        $this->cashierUser->branches()->sync([$this->branch1->id]);

        // Manajer Cabang 1
        $this->managerUser = User::factory()->create([
            'name' => 'Manajer Cabang 1',
            'email' => 'manajer1@example.com',
            'role_id' => $managerRole?->id,
        ]);
        $this->managerUser->branches()->sync([$this->branch1->id]);

        // Karyawan ke-2 di Cabang 1
        $otherEmpUser = User::factory()->create([
            'name' => 'Karyawan Dua',
            'email' => 'emp2@example.com',
            'role_id' => $employeeRole?->id,
        ]);
        $this->otherEmployeeBranch1 = Employee::create([
            'user_id' => $otherEmpUser->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Karyawan Dua',
            'phone' => '081299997777',
            'status' => 'active',
        ]);
        EmployeeShift::create([
            'employee_id' => $this->otherEmployeeBranch1->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $this->testDate,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Karyawan di Cabang 2
        $empBranch2User = User::factory()->create([
            'name' => 'Karyawan Cabang 2',
            'email' => 'empb2@example.com',
            'role_id' => $employeeRole?->id,
        ]);
        $this->employeeBranch2 = Employee::create([
            'user_id' => $empBranch2User->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Karyawan Cabang 2',
            'phone' => '081288886666',
            'status' => 'active',
        ]);
    }

    /**
     * Skenario 1: Pelanggan berhasil membuat booking langsung berstatus Confirmed (Terjadwal) tanpa konfirmasi manual.
     */
    public function test_01_pelanggan_berhasil_membuat_booking_langsung_terjadwal_tanpa_persetujuan_manual(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
            'notes' => 'Janji temu terkonfirmasi otomatis',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($booking);
        $response->assertRedirect(route('customer.booking.show', $booking));

        // Status langsung Confirmed (tidak tertahan di pending)
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);
    }

    /**
     * Skenario 2: Booking langsung muncul di antrean operasional cabang yang benar.
     */
    public function test_02_booking_muncul_pada_antrean_operasional_cabang_yang_benar(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $antrianService = app(AntrianLayananService::class);
        $dailyQueue = $antrianService->getDailyQueue($this->managerUser, $this->testDate, ['branch_id' => $this->branch1->id]);

        $this->assertTrue($dailyQueue->contains('id', $booking->id));
    }

    /**
     * Skenario 3: Booking dengan petugas tertentu muncul pada pekerjaan karyawan tersebut.
     */
    public function test_03_booking_dengan_petugas_muncul_pada_pekerjaan_karyawan_tersebut(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '11:00',
        ]);

        $antrianService = app(AntrianLayananService::class);
        $employeeUser = $this->employee->user;
        $this->assertNotNull($employeeUser);

        $tasks = $antrianService->getUnifiedDailyTasks($employeeUser, $this->testDate);
        $bookingTask = $tasks->firstWhere('id', $booking->id);

        $this->assertNotNull($bookingTask);
        $this->assertTrue($bookingTask['can_start']);
    }

    /**
     * Skenario 4: Booking tidak muncul sebagai pekerjaan pribadi karyawan lain.
     */
    public function test_04_booking_tidak_muncul_sebagai_pekerjaan_pribadi_karyawan_lain(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '12:00',
        ]);

        $antrianService = app(AntrianLayananService::class);
        $otherUser = $this->otherEmployeeBranch1->user;
        $this->assertNotNull($otherUser);

        $otherTasks = $antrianService->getUnifiedDailyTasks($otherUser, $this->testDate);
        $this->assertNull($otherTasks->firstWhere('id', $booking->id));
    }

    /**
     * Skenario 5: Booking tanpa petugas tetap dapat ditangani dengan aman (muncul di antrean cabang, employee_id null).
     */
    public function test_05_booking_tanpa_petugas_tercatat_pada_antrean_cabang_dan_dapat_ditangani_dengan_aman(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'booking_date' => $this->testDate,
            'booking_time' => '13:00',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->whereNull('employee_id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);

        // Muncul di antrean manajer cabang
        $antrianService = app(AntrianLayananService::class);
        $branchQueue = $antrianService->getDailyQueue($this->managerUser, $this->testDate, ['branch_id' => $this->branch1->id]);
        $this->assertTrue($branchQueue->contains('id', $booking->id));

        // Tidak di-claim sebagai pekerjaan pribadi oleh teknisi tertentu
        $emp1Tasks = $antrianService->getUnifiedDailyTasks($this->employee->user, $this->testDate);
        $this->assertNull($emp1Tasks->firstWhere('id', $booking->id));
    }

    /**
     * Skenario 6: Karyawan cabang lain tidak dapat menerima booking cabang tersebut.
     */
    public function test_06_karyawan_cabang_lain_tidak_dapat_menerima_booking_cabang_tersebut(): void
    {
        $response = $this->actingAs($this->customer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employeeBranch2->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $this->customer->id,
            'employee_id' => $this->employeeBranch2->id,
        ]);
    }

    /**
     * Skenario 7: Slot penuh dan jadwal di luar shift tetap ditolak.
     */
    public function test_07_slot_penuh_dan_jadwal_di_luar_shift_tetap_ditolak(): void
    {
        // Petugas shift: 09:00 - 16:00. Booking 08:00 harus ditolak.
        $response = $this->actingAs($this->customer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '08:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $this->customer->id,
            'booking_time' => '08:00',
        ]);
    }

    /**
     * Skenario 8: Booking bentrok jadwal tetap dicegah.
     */
    public function test_08_booking_bentrok_jadwal_tetap_dicegah(): void
    {
        // Booking 1: 10:00 - 11:00 untuk petugas ini
        $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        // Booking 2 oleh customer lain pada jam yang sama untuk petugas yang sama
        $response = $this->actingAs($this->otherCustomer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
    }

    /**
     * Skenario 9: Kasir tetap dapat melakukan check-in ketika pelanggan hadir.
     */
    public function test_09_kasir_dapat_melakukan_checkin_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '14:00',
        ]);

        $response = $this->actingAs($this->cashierUser)->postJson(route('pos.bookings.check-in', $booking));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'confirmed');
    }

    /**
     * Skenario 10: Karyawan dapat memulai dan menyelesaikan layanan.
     */
    public function test_10_karyawan_dapat_memulai_dan_menyelesaikan_layanan(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '15:00',
        ]);

        $statusService = app(StatusPekerjaanService::class);
        $employeeUser = $this->employee->user;

        // Mulai pengerjaan (In Progress)
        $statusService->updateBookingStatus($employeeUser, $booking, BookingStatus::InProgress->value);
        $this->assertEquals(BookingStatus::InProgress, $booking->fresh()->status);

        // Tandai Selesai (Completed)
        $statusService->updateBookingStatus($employeeUser, $booking, BookingStatus::Completed->value);
        $this->assertEquals(BookingStatus::Completed, $booking->fresh()->status);
    }

    /**
     * Skenario 11: Pembayaran tetap terpisah dari status pengerjaan booking.
     */
    public function test_11_pembayaran_tetap_terpisah_dari_status_pengerjaan_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        // Simulasikan transaksi kasir untuk booking ini
        $trx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-TEST-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 100000,
            'payment_method' => 'cash',
            'status' => StatusPembayaran::Paid,
            'paid_at' => now(),
        ]);

        // Pembayaran lunas tidak memaksa status booking menjadi Completed sebelum karyawan selesai mengerjakan
        $this->assertEquals(BookingStatus::Confirmed, $booking->fresh()->status);
        $this->assertEquals(StatusTransaksi::Paid, $trx->fresh()->status);
    }

    /**
     * Skenario 12: Pembatalan dan alasannya tetap berfungsi pada status Confirmed.
     */
    public function test_12_pembatalan_booking_dan_alasan_pembatalan_tetap_berfungsi(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '11:00',
        ]);

        $this->assertEquals(BookingStatus::Confirmed, $booking->status);

        $response = $this->actingAs($this->customer)->patch(route('customer.booking.cancel', $booking), [
            'reason_preset' => 'Ada keperluan mendadak',
        ]);

        $response->assertRedirect();
        $this->assertEquals(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertEquals('Ada keperluan mendadak', $booking->fresh()->cancellation_reason);
    }

    /**
     * Skenario 13: Booking historis berstatus Pending tetap dapat dibuka dan diproses.
     */
    public function test_13_booking_historis_berstatus_pending_tetap_dapat_dibuka_dan_diproses(): void
    {
        $historicalBooking = Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '14:00',
            'status' => BookingStatus::Pending,
        ]);

        // Customer dapat melihat detail
        $this->actingAs($this->customer)
            ->get(route('customer.booking.show', $historicalBooking))
            ->assertOk();

        // Manager dapat mengonfirmasi booking lama
        $statusService = app(StatusPekerjaanService::class);
        $statusService->updateBookingStatus($this->managerUser, $historicalBooking, BookingStatus::Confirmed->value);
        $this->assertEquals(BookingStatus::Confirmed, $historicalBooking->fresh()->status);
    }

    /**
     * Skenario 14: Alur Drop-off tidak berubah dan tetap menggunakan status pesanan.
     */
    public function test_14_alur_dropoff_tidak_berubah_dan_tetap_menggunakan_status_pesanan(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'status' => StatusPesanan::Pending,
            'notes' => 'Drop-off pakaian',
        ]);

        $statusService = app(StatusPekerjaanService::class);
        $statusService->updateOrderStatus($this->managerUser, $order, StatusPesanan::Received->value);

        $this->assertEquals(StatusPesanan::Received, $order->fresh()->status);
    }

    /**
     * Skenario 15: Notifikasi WhatsApp booking terjadwal dikirim tanpa duplikasi.
     */
    public function test_15_notifikasi_whatsapp_booking_terjadwal_dikirim_tanpa_duplikasi(): void
    {
        $this->customer->update(['phone' => '081234567890']);

        $response = $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($booking);

        // Hanya 1 log notifikasi booking_created dikirimkan
        $logs = NotificationLog::where('user_id', $this->customer->id)
            ->where('notifiable_id', $booking->id)
            ->get();

        $this->assertCount(1, $logs);
        $this->assertStringContainsString('BOOKING TERKONFIRMASI', $logs->first()->message);
        $this->assertStringContainsString('telah dikonfirmasi secara otomatis', $logs->first()->message);
    }

    /**
     * Skenario 16: Komisi dihitung ketika transaksi Paid dan pengerjaan booking Completed.
     */
    public function test_16_komisi_dan_laporan_tetap_menggunakan_transaksi_yang_sah(): void
    {
        $this->service60Min->update([
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 20000,
        ]);

        $booking = $this->bookingService->createBooking($this->customer, [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $trx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashierUser->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-COMM-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'service_id' => $this->service60Min->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);

        // Selesaikan pengerjaan oleh karyawan
        $statusService = app(StatusPekerjaanService::class);
        $statusService->updateBookingStatus($this->employee->user, $booking, BookingStatus::InProgress->value);
        $statusService->updateBookingStatus($this->employee->user, $booking, BookingStatus::Completed->value);

        // Komisi diproses melalui service komisi
        $commService = app(KomisiKaryawanService::class);
        $commService->processTransactionCommission($trx);

        $commissions = EmployeeCommission::where('employee_id', $this->employee->id)->get();
        $this->assertTrue($commissions->isNotEmpty());
        $this->assertEquals(StatusTransaksi::Paid, $trx->fresh()->status);
    }
}
