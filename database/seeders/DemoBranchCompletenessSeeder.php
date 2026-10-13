<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\EmployeeSkill;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoBranchCompletenessSeeder extends Seeder
{
    /**
     * Seeder kelengkapan data operasional 3 unit usaha SIMAUL.
     * Idempoten, aman, dan patuh isolasi cabang.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $today = Carbon::today();
        $todayStr = $today->toDateString();
        $yesterdayStr = $today->copy()->subDay()->toDateString();
        $tomorrowStr = $today->copy()->addDay()->toDateString();
        $dayAfterStr = $today->copy()->addDays(2)->toDateString();

        $customers = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))->orderBy('id')->get();
        $cashier = User::where('email', 'cashier@example.com')->first() ?? User::whereHas('role', fn ($q) => $q->where('name', 'cashier'))->first();
        $owner = User::where('email', 'owner@example.com')->first() ?? User::first();

        // ---------------------------------------------------------------------
        // 1. DATA USAHA A: BARBERSHOP & STUDIO (Cabang ID: 1, Appointment)
        // ---------------------------------------------------------------------
        $branch1 = Branch::find(1);
        if ($branch1) {
            $staffB1 = [
                ['name' => 'Bayu Pratama', 'phone' => '08110000101', 'skills' => ['Executive Haircut', 'Potong Rambut & Styling']],
                ['name' => 'Reza Gunawan', 'phone' => '08110000102', 'skills' => ['Pewarnaan Rambut', 'Scalp Treatment']],
                ['name' => 'Gilang Ramadhan', 'phone' => '08110000103', 'skills' => ['Cukur & Rapikan Jenggot', 'Paket Grooming Lengkap']],
            ];
            foreach ($staffB1 as $s) {
                $emp = Employee::firstOrCreate(['branch_id' => 1, 'name' => $s['name']], ['phone' => $s['phone'], 'status' => 'active']);
                foreach ($s['skills'] as $sk) {
                    EmployeeSkill::firstOrCreate(['employee_id' => $emp->id, 'skill_name' => $sk]);
                }
            }

            $allEmps1 = Employee::where('branch_id', 1)->where('status', 'active')->get();
            $shiftPlans1 = [
                ['start' => '09:00:00', 'end' => '17:00:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '10:00:00', 'end' => '18:00:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '11:00:00', 'end' => '19:00:00', 'status' => StatusShiftKaryawan::Scheduled],
                ['start' => '12:00:00', 'end' => '20:00:00', 'status' => StatusShiftKaryawan::Scheduled],
            ];
            foreach ($allEmps1 as $idx => $emp) {
                $plan = $shiftPlans1[$idx % count($shiftPlans1)];
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 1, 'shift_date' => $todayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => $plan['status']]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 1, 'shift_date' => $tomorrowStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Scheduled]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 1, 'shift_date' => $yesterdayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Present]);
            }

            // Bookings Barbershop (Hari ini)
            $srvPotong = Service::where('branch_id', 1)->where('name', 'like', '%Potong%')->first();
            $srvCukur = Service::where('branch_id', 1)->where('name', 'like', '%Cukur%')->first();
            $srvWarna = Service::where('branch_id', 1)->where('name', 'like', '%Warna%')->first();
            $actData1 = [
                ['time' => '09:30', 'cust' => $customers[0] ?? null, 'srv' => $srvPotong, 'emp' => $allEmps1[0] ?? null, 'status' => BookingStatus::Completed],
                ['time' => '11:00', 'cust' => $customers[1] ?? null, 'srv' => $srvCukur, 'emp' => $allEmps1[1] ?? null, 'status' => BookingStatus::InProgress],
                ['time' => '13:30', 'cust' => $customers[2] ?? null, 'srv' => $srvWarna, 'emp' => $allEmps1[2] ?? null, 'status' => BookingStatus::Confirmed],
                ['time' => '15:00', 'cust' => $customers[3] ?? null, 'srv' => $srvPotong, 'emp' => $allEmps1[3] ?? null, 'status' => BookingStatus::Confirmed],
                ['time' => '16:30', 'cust' => $customers[4] ?? null, 'srv' => $srvCukur, 'emp' => null, 'status' => BookingStatus::Confirmed],
            ];
            foreach ($actData1 as $ad) {
                if ($ad['cust'] && $ad['srv']) {
                    $bk = Booking::updateOrCreate(
                        ['branch_id' => 1, 'user_id' => $ad['cust']->id, 'booking_date' => $todayStr, 'booking_time' => $ad['time']],
                        ['service_id' => $ad['srv']->id, 'employee_id' => $ad['emp']?->id, 'status' => $ad['status'], 'notes' => 'Reservasi Barbershop Demo']
                    );
                    BookingDetail::updateOrCreate(['booking_id' => $bk->id, 'service_id' => $ad['srv']->id], ['price' => $ad['srv']->price]);
                }
            }

            // Transaksi Selesai Hari Ini Barbershop (Omzet)
            if ($cashier && $srvPotong && isset($actData1[0]['cust'])) {
                $firstBk = Booking::where('branch_id', 1)->where('booking_date', $todayStr)->first();
                $trx1 = Transaction::updateOrCreate(
                    ['transaction_code' => 'TRX-B1-'.$today->format('Ymd').'-01'],
                    ['branch_id' => 1, 'cashier_id' => $cashier->id, 'booking_id' => $firstBk?->id, 'subtotal' => 95000, 'discount' => 0, 'total' => 95000, 'status' => StatusTransaksi::Paid, 'transaction_date' => $today->copy()->setTime(10, 15)]
                );
                TransactionDetail::updateOrCreate(['transaction_id' => $trx1->id, 'service_id' => $srvPotong->id], ['qty' => 1, 'price' => 95000, 'subtotal' => 95000]);
                Payment::updateOrCreate(['transaction_id' => $trx1->id], ['amount' => 95000, 'payment_method' => 'cash', 'status' => StatusPembayaran::Paid, 'paid_at' => $today->copy()->setTime(10, 15)]);
            }
        }

        // ---------------------------------------------------------------------
        // 2. DATA USAHA B: LAUNDRY & SHOE CARE (Cabang ID: 2, Drop-off Saja)
        // ---------------------------------------------------------------------
        $branch2 = Branch::find(2);
        if ($branch2) {
            $staffB2 = [
                ['name' => 'Siti Aisyah', 'phone' => '08110000201', 'skills' => ['Cuci Kering Setrika', 'Dry Clean Jas & Kebaya']],
                ['name' => 'Budi Hartono', 'phone' => '08110000202', 'skills' => ['Deep Clean Sepatu', 'Cuci Bed Cover']],
                ['name' => 'Maya Indah', 'phone' => '08110000203', 'skills' => ['Laundry Express', 'Cuci Karpet']],
            ];
            foreach ($staffB2 as $s) {
                $emp = Employee::firstOrCreate(['branch_id' => 2, 'name' => $s['name']], ['phone' => $s['phone'], 'status' => 'active']);
                foreach ($s['skills'] as $sk) {
                    EmployeeSkill::firstOrCreate(['employee_id' => $emp->id, 'skill_name' => $sk]);
                }
            }

            $allEmps2 = Employee::where('branch_id', 2)->where('status', 'active')->get();
            $shiftPlans2 = [
                ['start' => '08:00:00', 'end' => '16:00:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '09:00:00', 'end' => '17:00:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '10:00:00', 'end' => '18:00:00', 'status' => StatusShiftKaryawan::Scheduled],
                ['start' => '12:00:00', 'end' => '20:00:00', 'status' => StatusShiftKaryawan::Scheduled],
            ];
            foreach ($allEmps2 as $idx => $emp) {
                $plan = $shiftPlans2[$idx % count($shiftPlans2)];
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 2, 'shift_date' => $todayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => $plan['status']]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 2, 'shift_date' => $tomorrowStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Scheduled]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 2, 'shift_date' => $yesterdayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Present]);
            }

            // Drop-off Orders Laundry (Hari Ini)
            $srvKering = Service::where('branch_id', 2)->where('name', 'like', '%Kering%')->first();
            $srvSepatu = Service::where('branch_id', 2)->where('name', 'like', '%Sepatu%')->first();
            $srvDry = Service::where('branch_id', 2)->where('name', 'like', '%Dry%')->first();
            $actData2 = [
                ['time' => '08:30', 'cust' => $customers[5] ?? null, 'srv' => $srvKering, 'emp' => $allEmps2[0] ?? null, 'status' => StatusPesanan::Completed],
                ['time' => '10:00', 'cust' => $customers[6] ?? null, 'srv' => $srvSepatu, 'emp' => $allEmps2[1] ?? null, 'status' => StatusPesanan::Ready],
                ['time' => '11:30', 'cust' => $customers[7] ?? null, 'srv' => $srvDry, 'emp' => $allEmps2[2] ?? null, 'status' => StatusPesanan::Processing],
                ['time' => '13:00', 'cust' => $customers[8] ?? null, 'srv' => $srvKering, 'emp' => $allEmps2[3] ?? null, 'status' => StatusPesanan::Received],
                ['time' => '14:30', 'cust' => $customers[9] ?? null, 'srv' => $srvSepatu, 'emp' => null, 'status' => StatusPesanan::Received],
            ];
            foreach ($actData2 as $ad) {
                if ($ad['cust'] && $ad['srv']) {
                    $order = Order::updateOrCreate(
                        ['branch_id' => 2, 'user_id' => $ad['cust']->id, 'notes' => 'Pesanan Laundry Demo '.$ad['time']],
                        ['service_id' => $ad['srv']->id, 'employee_id' => $ad['emp']?->id, 'estimated_quantity' => 2, 'pickup_option' => true, 'status' => $ad['status'], 'created_at' => $today->copy()->setTime((int) substr($ad['time'], 0, 2), (int) substr($ad['time'], 3, 2))]
                    );
                    OrderDetail::updateOrCreate(['order_id' => $order->id, 'service_id' => $ad['srv']->id], ['price' => $ad['srv']->price, 'qty' => 2, 'subtotal' => $ad['srv']->price * 2]);
                }
            }

            // Transaksi Selesai Hari Ini Laundry
            if ($cashier && $srvKering && isset($actData2[0]['cust'])) {
                $firstOrd = Order::where('branch_id', 2)->whereDate('created_at', $todayStr)->first();
                $trx2 = Transaction::updateOrCreate(
                    ['transaction_code' => 'TRX-B2-'.$today->format('Ymd').'-01'],
                    ['branch_id' => 2, 'cashier_id' => $cashier->id, 'order_id' => $firstOrd?->id, 'subtotal' => 130000, 'discount' => 0, 'total' => 130000, 'status' => StatusTransaksi::Paid, 'transaction_date' => $today->copy()->setTime(9, 30)]
                );
                TransactionDetail::updateOrCreate(['transaction_id' => $trx2->id, 'service_id' => $srvKering->id], ['qty' => 2, 'price' => 65000, 'subtotal' => 130000]);
                Payment::updateOrCreate(['transaction_id' => $trx2->id], ['amount' => 130000, 'payment_method' => 'qris', 'status' => StatusPembayaran::Paid, 'paid_at' => $today->copy()->setTime(9, 30)]);
            }
        }

        // ---------------------------------------------------------------------
        // 3. DATA USAHA C: AUTO CARE & DETAILING (Cabang ID: 3, Both)
        // ---------------------------------------------------------------------
        $branch3 = Branch::find(3);
        if ($branch3) {
            $staffB3 = [
                ['name' => 'Eko Prasetyo', 'phone' => '08110000301', 'skills' => ['Cuci Mobil Premium', 'Detailing Mobil Lengkap']],
                ['name' => 'Wahyu Hidayat', 'phone' => '08110000302', 'skills' => ['Nano Ceramic Coating', 'Perawatan Eksterior Mobil']],
                ['name' => 'Doni Kurnia', 'phone' => '08110000303', 'skills' => ['Detailing Interior', 'Cuci Helm']],
            ];
            foreach ($staffB3 as $s) {
                $emp = Employee::firstOrCreate(['branch_id' => 3, 'name' => $s['name']], ['phone' => $s['phone'], 'status' => 'active']);
                foreach ($s['skills'] as $sk) {
                    EmployeeSkill::firstOrCreate(['employee_id' => $emp->id, 'skill_name' => $sk]);
                }
            }

            $allEmps3 = Employee::where('branch_id', 3)->where('status', 'active')->get();
            $shiftPlans3 = [
                ['start' => '08:30:00', 'end' => '16:30:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '08:30:00', 'end' => '16:30:00', 'status' => StatusShiftKaryawan::Present],
                ['start' => '09:30:00', 'end' => '17:30:00', 'status' => StatusShiftKaryawan::Scheduled],
                ['start' => '10:00:00', 'end' => '18:00:00', 'status' => StatusShiftKaryawan::Scheduled],
            ];
            foreach ($allEmps3 as $idx => $emp) {
                $plan = $shiftPlans3[$idx % count($shiftPlans3)];
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 3, 'shift_date' => $todayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => $plan['status']]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 3, 'shift_date' => $tomorrowStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Scheduled]);
                EmployeeShift::updateOrCreate(['employee_id' => $emp->id, 'branch_id' => 3, 'shift_date' => $yesterdayStr], ['start_time' => $plan['start'], 'end_time' => $plan['end'], 'status' => StatusShiftKaryawan::Present]);
            }

            // Aktivitas Auto Care: Gabungan Appointment dan Drop-off
            $srvCuciMbl = Service::where('branch_id', 3)->where('name', 'like', '%Cuci Mobil%')->first();
            $srvDetail = Service::where('branch_id', 3)->where('name', 'like', '%Detailing%')->first();
            $srvCoating = Service::where('branch_id', 3)->where('name', 'like', '%Coating%')->first();

            // Bookings Auto Care
            if ($srvCuciMbl && isset($customers[10])) {
                $bk3_1 = Booking::updateOrCreate(
                    ['branch_id' => 3, 'user_id' => $customers[10]->id, 'booking_date' => $todayStr, 'booking_time' => '09:00'],
                    ['service_id' => $srvCuciMbl->id, 'employee_id' => $allEmps3[0]?->id, 'status' => BookingStatus::Completed, 'notes' => 'Cuci Mobil Premium SUV']
                );
                BookingDetail::updateOrCreate(['booking_id' => $bk3_1->id, 'service_id' => $srvCuciMbl->id], ['price' => $srvCuciMbl->price]);
            }
            if ($srvCoating && isset($customers[11])) {
                $bk3_2 = Booking::updateOrCreate(
                    ['branch_id' => 3, 'user_id' => $customers[11]->id, 'booking_date' => $todayStr, 'booking_time' => '11:00'],
                    ['service_id' => $srvCoating->id, 'employee_id' => $allEmps3[1]?->id, 'status' => BookingStatus::InProgress, 'notes' => 'Nano Ceramic Coating Sedan']
                );
                BookingDetail::updateOrCreate(['booking_id' => $bk3_2->id, 'service_id' => $srvCoating->id], ['price' => $srvCoating->price]);
            }

            // Drop-off Auto Care
            if ($srvDetail && isset($customers[12])) {
                $ord3_1 = Order::updateOrCreate(
                    ['branch_id' => 3, 'user_id' => $customers[12]->id, 'notes' => 'Drop-off Detailing Interior'],
                    ['service_id' => $srvDetail->id, 'employee_id' => $allEmps3[2]?->id, 'estimated_quantity' => 1, 'pickup_option' => true, 'status' => StatusPesanan::Processing, 'created_at' => $today->copy()->setTime(10, 0)]
                );
                OrderDetail::updateOrCreate(['order_id' => $ord3_1->id, 'service_id' => $srvDetail->id], ['price' => $srvDetail->price, 'qty' => 1, 'subtotal' => $srvDetail->price]);
            }
            if ($srvCuciMbl && isset($customers[13])) {
                $ord3_2 = Order::updateOrCreate(
                    ['branch_id' => 3, 'user_id' => $customers[13]->id, 'notes' => 'Drop-off Cuci Helm'],
                    ['service_id' => $srvCuciMbl->id, 'employee_id' => null, 'estimated_quantity' => 1, 'pickup_option' => true, 'status' => StatusPesanan::Received, 'created_at' => $today->copy()->setTime(14, 0)]
                );
                OrderDetail::updateOrCreate(['order_id' => $ord3_2->id, 'service_id' => $srvCuciMbl->id], ['price' => $srvCuciMbl->price, 'qty' => 1, 'subtotal' => $srvCuciMbl->price]);
            }

            // Transaksi Selesai Hari Ini Auto Care
            if ($cashier && $srvCuciMbl && isset($customers[10])) {
                $firstBk3 = Booking::where('branch_id', 3)->where('booking_date', $todayStr)->first();
                $trx3 = Transaction::updateOrCreate(
                    ['transaction_code' => 'TRX-B3-'.$today->format('Ymd').'-01'],
                    ['branch_id' => 3, 'cashier_id' => $cashier->id, 'booking_id' => $firstBk3?->id, 'subtotal' => 90000, 'discount' => 0, 'total' => 90000, 'status' => StatusTransaksi::Paid, 'transaction_date' => $today->copy()->setTime(10, 0)]
                );
                TransactionDetail::updateOrCreate(['transaction_id' => $trx3->id, 'service_id' => $srvCuciMbl->id], ['qty' => 1, 'price' => 90000, 'subtotal' => 90000]);
                Payment::updateOrCreate(['transaction_id' => $trx3->id], ['amount' => 90000, 'payment_method' => 'cash', 'status' => StatusPembayaran::Paid, 'paid_at' => $today->copy()->setTime(10, 0)]);
            }
        }
    }
}
