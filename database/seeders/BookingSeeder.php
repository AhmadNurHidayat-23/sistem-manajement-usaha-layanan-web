<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Jalankan seeder booking appointment dan order drop-off sesuai cabang dan mode operasionalnya.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $employeeA = Employee::where('branch_id', $branchA->id)->firstOrFail();
        $employeeC = Employee::where('branch_id', $branchC->id)->firstOrFail();

        $serviceBarber1 = Service::where('name', 'Potong Rambut Pria')->where('branch_id', $branchA->id)->firstOrFail();
        $serviceBarber2 = Service::where('name', 'Cukur & Rapikan Jenggot')->where('branch_id', $branchA->id)->firstOrFail();
        $serviceLaundry = Service::where('name', 'Deep Clean Sepatu')->where('branch_id', $branchB->id)->firstOrFail();
        $serviceAuto = Service::where('name', 'Cuci Mobil Premium')->where('branch_id', $branchC->id)->firstOrFail();

        // 1. Cabang A - Pusat (Appointment): Booking Selesai Kemarin
        $booking1 = Booking::firstOrCreate(
            [
                'user_id' => $customer->id,
                'service_id' => $serviceBarber1->id,
                'booking_date' => Carbon::yesterday()->toDateString(),
            ],
            [
                'branch_id' => $branchA->id,
                'employee_id' => $employeeA->id,
                'booking_time' => '11:00',
                'status' => BookingStatus::Completed,
                'notes' => 'Potong rapi gaya fade samping, finishing pomade matte.',
            ]
        );

        BookingDetail::firstOrCreate(
            [
                'booking_id' => $booking1->id,
                'service_id' => $serviceBarber1->id,
            ],
            [
                'price' => 95000,
            ]
        );

        // 2. Cabang A - Pusat (Appointment): Booking Hari Ini
        $booking2 = Booking::firstOrCreate(
            [
                'user_id' => $customer->id,
                'service_id' => $serviceBarber2->id,
                'booking_date' => Carbon::today()->toDateString(),
            ],
            [
                'branch_id' => $branchA->id,
                'employee_id' => $employeeA->id,
                'booking_time' => '14:00',
                'status' => BookingStatus::InProgress,
                'notes' => 'Rapikan jenggot dan kumis, hot towel aromaterapi.',
            ]
        );

        BookingDetail::firstOrCreate(
            [
                'booking_id' => $booking2->id,
                'service_id' => $serviceBarber2->id,
            ],
            [
                'price' => 65000,
            ]
        );

        // 3. Cabang B - Selatan (Drop-off): Order Drop-off Sepatu Sedang Diproses
        $order1 = Order::firstOrCreate(
            [
                'user_id' => $customer->id,
                'service_id' => $serviceLaundry->id,
            ],
            [
                'branch_id' => $branchB->id,
                'estimated_quantity' => 2,
                'pickup_option' => true,
                'estimated_finish_at' => Carbon::today()->addHours(6),
                'status' => StatusPesanan::Processing,
                'notes' => '2 Pasang sepatu: 1 sneakers canvas putih & 1 sepatu kulit hitam.',
            ]
        );

        OrderDetail::firstOrCreate(
            [
                'order_id' => $order1->id,
                'service_id' => $serviceLaundry->id,
            ],
            [
                'price' => 80000,
                'qty' => 2,
                'subtotal' => 160000,
            ]
        );

        // 4. Cabang C - Barat (Both): Booking Cuci Mobil Hari Ini
        $booking3 = Booking::firstOrCreate(
            [
                'user_id' => $customer->id,
                'service_id' => $serviceAuto->id,
                'booking_date' => Carbon::today()->toDateString(),
            ],
            [
                'branch_id' => $branchC->id,
                'employee_id' => $employeeC->id,
                'booking_time' => '16:00',
                'status' => BookingStatus::Confirmed,
                'notes' => 'Mobil SUV Hitam, mohon fogging interior lebih intensif.',
            ]
        );

        BookingDetail::firstOrCreate(
            [
                'booking_id' => $booking3->id,
                'service_id' => $serviceAuto->id,
            ],
            [
                'price' => 90000,
            ]
        );
    }
}
