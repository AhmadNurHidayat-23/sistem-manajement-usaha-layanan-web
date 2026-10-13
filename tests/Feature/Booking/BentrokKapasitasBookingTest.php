<?php

namespace Tests\Feature\Booking;

use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\EmployeeShift;
use App\Models\Service;
use Tests\Feature\Booking\Support\ShiftBookingTestCase;

class BentrokKapasitasBookingTest extends ShiftBookingTestCase
{
    public function test_bentrok_booking_petugas_pada_rentang_durasi_yang_sama_ditolak(): void
    {
        // Booking pertama untuk petugas: 10:00 - 11:00 (durasi 60 min)
        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);
        $this->assertEquals(1, Booking::count());

        // Booking kedua mencoba memesan petugas yang sama pada 10:30 (10:30 - 11:30)
        $response = $this->actingAs($this->otherCustomer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah memiliki jadwal pada rentang waktu', session('error'));
        $this->assertEquals(1, Booking::count());
    }

    public function test_bentrok_booking_petugas_lintas_cabang_ditolak(): void
    {
        $serviceBranch2 = Service::create([
            'service_category_id' => $this->service60Min->service_category_id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Cabang 2',
            'price' => 80000,
            'estimated_duration' => 60,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        // Booking pertama petugas di Cabang 1: 10:00 - 11:00
        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);
        $this->assertEquals(1, Booking::count());

        // Buat shift di Cabang 2 agar shift valid
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch2->id,
            'shift_date' => $this->testDate,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        // Percobaan booking petugas yang sama di Cabang 2 pada 10:30 (overlap)
        $response = $this->actingAs($this->otherCustomer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch2->id,
            'service_id' => $serviceBranch2->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah memiliki jadwal pada rentang waktu', session('error'));
        $this->assertEquals(1, Booking::count());
    }

    public function test_booking_pada_batas_waktu_antar_slot_tidak_bentrok(): void
    {
        // Booking 1: 10:00 - 11:00 (durasi 60 min)
        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);
        $this->assertEquals(1, Booking::count());

        // Booking 2: tepat setelahnya 11:00 - 12:00 (tidak bentrok)
        $resAfter = $this->actingAs($this->otherCustomer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '11:00',
        ]);
        $resAfter->assertSessionHas('success');
        $this->assertEquals(2, Booking::count());

        // Booking 3: tepat sebelumnya 09:00 - 10:00 (tidak bentrok)
        $resBefore = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '09:00',
        ]);
        $resBefore->assertSessionHas('success');
        $this->assertEquals(3, Booking::count());
    }

    public function test_booking_tanpa_pemilihan_petugas_berhasil_sesuai_kapasitas_cabang(): void
    {
        // Petugas opsional (BOK-F-001)
        $response = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => null,
            'booking_date' => $this->testDate,
            'booking_time' => '13:00',
        ]);

        $booking = Booking::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($booking);
        $this->assertNull($booking->employee_id);
        $response->assertRedirect(route('bookings.show', $booking));
    }

    public function test_booking_tanpa_petugas_ditolak_jika_kapasitas_cabang_penuh_pada_rentang_durasi(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);

        // Booking 1: 10:00 - 11:00 (durasi 60 min, memakan slot 10:00 dan 10:30)
        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);
        $this->assertEquals(1, Booking::count());

        // Booking 2 mencoba memesan di 10:30 tanpa petugas
        $response = $this->actingAs($this->otherCustomer)->from(route('bookings.create'))->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service30Min->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:30',
        ]);

        $response->assertRedirect(route('bookings.create'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah penuh', session('error'));
        $this->assertEquals(1, Booking::count());
    }
}
