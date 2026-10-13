<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\EmployeeShift;
use Tests\Feature\Booking\Support\ShiftBookingTestCase;

class JadwalShiftBookingTest extends ShiftBookingTestCase
{
    public function test_booking_valid_di_dalam_shift_karyawan_berhasil_pada_web(): void
    {
        $response = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
            'notes' => 'Booking reguler',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($booking);
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertEquals($this->employee->id, $booking->employee_id);
        $this->assertEquals('10:00', substr((string) $booking->booking_time, 0, 5));
        $this->assertEquals(BookingStatus::Pending, $booking->status);
    }

    public function test_booking_ditolak_jika_dimulai_sebelum_shift_kerja_petugas(): void
    {
        // Shift petugas: 09:00 - 16:00, jam booking 08:30 (cabang buka 08:00, tapi shift mulai 09:00)
        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '08:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar jam kerja shift petugas', session('error'));
        $this->assertEquals(0, Booking::count());
    }

    public function test_booking_ditolak_jika_dimulai_setelah_shift_kerja_petugas(): void
    {
        // Shift petugas berakhir 16:00, cabang buka s/d 19:00, jam booking 16:30
        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '16:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar jam kerja shift petugas', session('error'));
        $this->assertEquals(0, Booking::count());
    }

    public function test_booking_ditolak_jika_dimulai_dalam_shift_tetapi_durasi_selesai_melewati_batas_shift(): void
    {
        // Shift: 09:00 - 16:00. Layanan 60 menit. Booking mulai 15:30 (selesai 16:30 > 16:00 shift end, tapi <= 19:00 cabang)
        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '15:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('di luar jam kerja shift petugas', session('error'));
        $this->assertEquals(0, Booking::count());
    }

    public function test_booking_ditolak_jika_petugas_tidak_memiliki_shift_pada_tanggal_tersebut(): void
    {
        $differentDate = now()->addDays(10)->toDateString();

        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $differentDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Petugas tidak memiliki jadwal kerja aktif pada tanggal dan cabang yang dipilih', session('error'));
        $this->assertEquals(0, Booking::count());
    }

    public function test_booking_ditolak_jika_petugas_memiliki_shift_di_cabang_lain_pada_tanggal_tersebut(): void
    {
        $anotherDate = now()->addDays(6)->toDateString();

        // Petugas dijadwalkan di Cabang 2
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch2->id,
            'shift_date' => $anotherDate,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Customer mencoba booking petugas di Cabang 1
        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $anotherDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Petugas tidak memiliki jadwal kerja aktif pada tanggal dan cabang yang dipilih', session('error'));
        $this->assertEquals(0, Booking::count());
    }

    public function test_booking_ditolak_jika_shift_petugas_berstatus_off_atau_absent(): void
    {
        $offDate = now()->addDays(7)->toDateString();

        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $offDate,
            'start_time' => '09:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Off,
        ]);

        $response = $this->actingAs($this->customer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $offDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Petugas tidak memiliki jadwal kerja aktif', session('error'));
        $this->assertEquals(0, Booking::count());
    }
}
