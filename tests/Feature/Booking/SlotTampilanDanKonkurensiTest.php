<?php

namespace Tests\Feature\Booking;

use App\Enums\StatusShiftKaryawan;
use App\Models\Booking;
use App\Models\EmployeeShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Booking\Support\ShiftBookingTestCase;

class SlotTampilanDanKonkurensiTest extends ShiftBookingTestCase
{
    public function test_get_slots_with_status_menandai_status_tersedia_penuh_lewat_dan_di_luar_shift(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);

        // Buat booking pada 10:00 (durasi 60 min, slot 10:00 dan 10:30 terisi)
        Booking::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $slots = $this->bookingService->getSlotsWithStatus(
            $this->branch1,
            $this->testDate,
            null,
            $this->employee->id,
            $this->service60Min->id
        );

        $slotsByTime = collect($slots)->keyBy('time');

        // Petugas shift 09:00 - 16:00
        // 08:00 dan 08:30 di luar shift petugas
        $this->assertTrue($slotsByTime->has('08:00'));
        $this->assertEquals('outside_shift', $slotsByTime['08:00']['status']);
        $this->assertFalse($slotsByTime['08:00']['is_available']);

        // 09:00 tersedia (09:00 - 10:00 tidak bentrok dengan booking 10:00)
        $this->assertTrue($slotsByTime->has('09:00'));
        $this->assertEquals('available', $slotsByTime['09:00']['status']);
        $this->assertTrue($slotsByTime['09:00']['is_available']);

        // 10:00 sudah dipesan / penuh
        $this->assertTrue($slotsByTime->has('10:00'));
        $this->assertEquals('full', $slotsByTime['10:00']['status']);
        $this->assertTrue($slotsByTime['10:00']['is_full']);
        $this->assertFalse($slotsByTime['10:00']['is_available']);

        // 10:30 bentrok dengan booking 10:00 - 11:00 -> penuh
        $this->assertTrue($slotsByTime->has('10:30'));
        $this->assertEquals('full', $slotsByTime['10:30']['status']);
        $this->assertTrue($slotsByTime['10:30']['is_full']);

        // 11:00 tersedia
        $this->assertTrue($slotsByTime->has('11:00'));
        $this->assertEquals('available', $slotsByTime['11:00']['status']);
        $this->assertTrue($slotsByTime['11:00']['is_available']);

        // 15:30 + 60 min = 16:30 > 16:00 (selesai shift) -> outside_shift
        $this->assertTrue($slotsByTime->has('15:30'));
        $this->assertEquals('outside_shift', $slotsByTime['15:30']['status']);
    }

    public function test_slot_waktu_lampau_pada_hari_ini_diberi_status_past(): void
    {
        $today = now()->toDateString();

        // Buat shift hari ini untuk employee
        EmployeeShift::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch1->id,
            'shift_date' => $today,
            'start_time' => '00:00',
            'end_time' => '23:59',
            'status' => StatusShiftKaryawan::Scheduled,
        ]);

        $slots = $this->bookingService->getSlotsWithStatus(
            $this->branch1,
            $today,
            null,
            null,
            $this->service30Min->id
        );

        $this->assertNotEmpty($slots);
        $now = now();

        foreach ($slots as $slot) {
            $slotCarbon = Carbon::parse($today.' '.$slot['time']);
            if ($slotCarbon->lte($now)) {
                $this->assertEquals('past', $slot['status'], "Slot {$slot['time']} seharusnya past.");
                $this->assertTrue($slot['is_past']);
                $this->assertFalse($slot['is_available']);
            }
        }
    }

    public function test_pelanggan_available_slots_endpoint_mengembalikan_json_status_lengkap(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('customer.booking.available-slots', [
            'branch_id' => $this->branch1->id,
            'date' => $this->testDate,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'is_open',
            'slots' => [
                '*' => [
                    'time',
                    'status',
                    'is_available',
                    'is_full',
                    'is_past',
                    'is_outside_shift',
                    'booked_count',
                    'max_capacity',
                    'remaining_capacity',
                    'message',
                ],
            ],
        ]);
        $this->assertTrue($response->json('is_open'));
    }

    public function test_mobile_available_slots_endpoint_mengembalikan_slots_dan_slots_detailed(): void
    {
        $login = $this->postJson('/api/mobile/login', [
            'email' => $this->customer->email,
            'password' => 'password123',
        ]);
        $token = $login->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/customer/bookings/available-slots?'.http_build_query([
                'branch_id' => $this->branch1->id,
                'date' => $this->testDate,
                'service_id' => $this->service60Min->id,
                'employee_id' => $this->employee->id,
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'is_open' => true,
            ],
        ]);
        $response->assertJsonStructure([
            'data' => [
                'slots',
                'slots_detailed',
            ],
        ]);
    }

    public function test_backend_menolak_booking_pada_slot_yang_sudah_penuh(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);

        // Booking 1 berhasil
        $this->actingAs($this->customer)->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);
        $this->assertEquals(1, Booking::count());

        // Booking 2 mencoba jam yang sama ditolak
        $response = $this->actingAs($this->otherCustomer)->from(route('customer.booking.create'))->post(route('customer.booking.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service60Min->id,
            'employee_id' => $this->employee->id,
            'booking_date' => $this->testDate,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect(route('customer.booking.create'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, Booking::count());
    }

    public function test_backend_mencegah_booking_ganda_secara_atomik_pada_transaksi_konkuren(): void
    {
        $this->branch1->update(['max_slot_capacity' => 1]);

        // Simulasikan transaksi bersamaan:
        // Transaksi 1 berhasil memesan
        DB::transaction(function () {
            $this->bookingService->createBooking($this->customer, [
                'branch_id' => $this->branch1->id,
                'service_id' => $this->service60Min->id,
                'employee_id' => $this->employee->id,
                'booking_date' => $this->testDate,
                'booking_time' => '11:00',
            ]);
        });

        $this->assertEquals(1, Booking::where('booking_time', '11:00')->count());

        // Transaksi 2 berikutnya yang memproses slot yang sama langsung dilempar RuntimeException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/(sudah memiliki jadwal|sudah penuh)/');

        DB::transaction(function () {
            $this->bookingService->createBooking($this->otherCustomer, [
                'branch_id' => $this->branch1->id,
                'service_id' => $this->service60Min->id,
                'employee_id' => $this->employee->id,
                'booking_date' => $this->testDate,
                'booking_time' => '11:00',
            ]);
        });
    }

    public function test_halaman_create_booking_pelanggan_menampilkan_legend_dan_pilihan_waktu(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.booking.create'));

        $response->assertStatus(200);
        $response->assertSee('Waktu Booking Tersedia');
        $response->assertSee('Status Jam:');
        $response->assertSee('Tersedia');
        $response->assertSee('Dipilih');
        $response->assertSee('Penuh');
        $response->assertSee('Lewat / Tutup');
        $response->assertSee('slots-container');
        $response->assertSee('selected-slot-preview');
    }
}
