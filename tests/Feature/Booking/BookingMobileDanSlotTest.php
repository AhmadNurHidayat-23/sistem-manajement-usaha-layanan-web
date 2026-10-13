<?php

namespace Tests\Feature\Booking;

use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\EmployeeShift;
use App\Models\Transaction;
use Tests\Feature\Booking\Support\ShiftBookingTestCase;

class BookingMobileDanSlotTest extends ShiftBookingTestCase
{
    public function test_booking_valid_di_dalam_shift_karyawan_berhasil_pada_mobile(): void
    {
        $token = $this->getCustomerMobileToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/bookings', [
                'branch_id' => $this->branch1->id,
                'service_id' => $this->service60Min->id,
                'employee_id' => $this->employee->id,
                'booking_date' => $this->testDate,
                'booking_time' => '11:00',
                'notes' => 'Mobile booking',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $booking = Booking::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals($this->employee->id, $booking->employee_id);
        $this->assertEquals('11:00', substr((string) $booking->booking_time, 0, 5));

        $transaction = Transaction::where('booking_id', $booking->id)->first();
        $this->assertNotNull($transaction);
    }

    public function test_penolakan_request_tidak_valid_melalui_mobile_mengembalikan_422_tanpa_booking_parsial(): void
    {
        $token = $this->getCustomerMobileToken();

        // Booking di luar jam shift (15:30 untuk layanan 60 min padahal shift selesai 16:00)
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/customer/bookings', [
                'branch_id' => $this->branch1->id,
                'service_id' => $this->service60Min->id,
                'employee_id' => $this->employee->id,
                'booking_date' => $this->testDate,
                'booking_time' => '15:30',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        // Pastikan tidak ada booking dan tidak ada transaksi parsial
        $this->assertEquals(0, Booking::count());
        $this->assertEquals(0, Transaction::count());
    }

    public function test_get_available_slots_memfilter_slot_berdasarkan_shift_durasi_dan_bentrok_petugas(): void
    {
        // Tanpa petugas, mengembalikan seluruh slot jam buka cabang (08:00 - 19:00) dengan durasi 60 min
        $allSlots = $this->bookingService->getAvailableSlots(
            $this->branch1,
            $this->testDate,
            null,
            null,
            $this->service60Min->id
        );
        $this->assertContains('08:00', $allSlots);
        $this->assertContains('18:00', $allSlots);
        $this->assertNotContains('18:30', $allSlots); // 18:30 + 60 min = 19:30 > 19:00 tutup

        // Dengan petugas (shift 09:00 - 16:00):
        $empSlots = $this->bookingService->getAvailableSlots(
            $this->branch1,
            $this->testDate,
            null,
            $this->employee->id,
            $this->service60Min->id
        );
        $this->assertNotContains('08:00', $empSlots); // Petugas baru masuk 09:00
        $this->assertNotContains('08:30', $empSlots);
        $this->assertContains('09:00', $empSlots);
        $this->assertContains('10:00', $empSlots);
        $this->assertContains('15:00', $empSlots);
        $this->assertNotContains('15:30', $empSlots); // 15:30 + 60 min = 16:30 > 16:00 selesai shift

        // Buat booking pada 10:00 - 11:00 untuk petugas
        $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        // Cek kembali ketersediaan slot petugas setelah ada booking 10:00 - 11:00
        $updatedSlots = $this->bookingService->getAvailableSlots(
            $this->branch1,
            $this->testDate,
            null,
            $this->employee->id,
            $this->service60Min->id
        );

        // 09:00 selesai 10:00 (tidak bentrok) -> Tersedia
        $this->assertContains('09:00', $updatedSlots);
        // 09:30 selesai 10:30 (bentrok dengan 10:00 - 11:00) -> Harus difilter
        $this->assertNotContains('09:30', $updatedSlots);
        // 10:00 bentrok -> Harus difilter
        $this->assertNotContains('10:00', $updatedSlots);
        // 10:30 bentrok -> Harus difilter
        $this->assertNotContains('10:30', $updatedSlots);
        // 11:00 mulai saat booking pertama selesai -> Tersedia
        $this->assertContains('11:00', $updatedSlots);

        // Petugas off / libur mengembalikan array kosong
        $offDate = now()->addDays(8)->toDateString();
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $offDate,
            'start_time' => '09:00',
            'end_time' => '16:00',
            'status' => StatusShiftKaryawan::Off,
        ]);

        $offSlots = $this->bookingService->getAvailableSlots(
            $this->branch1,
            $offDate,
            null,
            $this->employee->id,
            $this->service60Min->id
        );
        $this->assertEmpty($offSlots);
    }
}
