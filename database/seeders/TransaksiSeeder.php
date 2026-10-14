<?php

namespace Database\Seeders;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Jalankan seeder transaksi kasir, pembayaran, penggunaan promo, dan komisi staf.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $cashier = User::whereIn('email', ['kasir@simaul.com', 'kasir.andi@simaul.com', 'cashier@example.com'])->firstOrFail();
        $customer = User::whereIn('email', ['pelanggan@simaul.com', 'customer@example.com'])->firstOrFail();
        $employeeA = Employee::where('branch_id', $branchA->id)->firstOrFail();

        $serviceA = Service::where('name', 'Potong Rambut Pria')->where('branch_id', $branchA->id)->firstOrFail();
        $bookingA = Booking::where('user_id', $customer->id)->where('service_id', $serviceA->id)->firstOrFail();
        $promo = Promotion::where('code', 'DISKON10')->firstOrFail();

        // 1. Transaksi Selesai & Lunas di Cabang A (Pusat)
        $transaction1 = Transaction::firstOrCreate(
            ['transaction_code' => 'TRX-DEMO-001'],
            [
                'branch_id' => $branchA->id,
                'cashier_id' => $cashier->id,
                'booking_id' => $bookingA->id,
                'subtotal' => 95000,
                'discount' => 9500,
                'total' => 85500,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => Carbon::yesterday()->setTime(11, 45),
            ]
        );

        // 2. Rincian Item Transaksi
        TransactionDetail::firstOrCreate(
            [
                'transaction_id' => $transaction1->id,
                'service_id' => $serviceA->id,
            ],
            [
                'qty' => 1,
                'price' => 95000,
                'subtotal' => 95000,
            ]
        );

        // 3. Catatan Pembayaran Tunai
        Payment::firstOrCreate(
            ['transaction_id' => $transaction1->id],
            [
                'payment_method' => 'cash',
                'amount' => 85500,
                'status' => StatusPembayaran::Completed,
                'reference_number' => 'Tunai | Bayar: Rp 100.000 | Kembali: Rp 14.500',
                'paid_at' => Carbon::yesterday()->setTime(11, 50),
            ]
        );

        // 4. Riwayat Penggunaan Promo
        PromotionUsage::firstOrCreate(
            ['transaction_id' => $transaction1->id],
            [
                'promotion_id' => $promo->id,
                'user_id' => $customer->id,
                'used_at' => Carbon::yesterday()->setTime(11, 45),
            ]
        );

        // 5. Komisi Stylist Barber
        EmployeeCommission::firstOrCreate(
            ['transaction_id' => $transaction1->id],
            [
                'employee_id' => $employeeA->id,
                'service_id' => $serviceA->id,
                'commission_type' => CommissionType::Percentage,
                'commission_value' => 10,
                'commission_amount' => 8550,
                'status' => CommissionStatus::Approved,
            ]
        );
    }
}
