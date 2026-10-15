<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\CashierShiftStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\CashierShift;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\MembershipPromo\MemberService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoKasirSeeder extends Seeder
{
    /**
     * Siapkan akun kasir demo, fixture pelanggan, transaksi, shift, dan booking/order per usaha.
     * Idempoten: aman dijalankan berulang tanpa merusak data historis atau duplikasi.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $rawPass = (string) (env('DEMO_CASHIER_PASSWORD') ?: config('auth.demo_cashier_password'));
        if ($rawPass === '') {
            $rawPass = 'kasir_demo_'.hash('crc32', (string) config('app.key'));
        }
        $passwordHash = Hash::make($rawPass);

        $now = Carbon::now('Asia/Jakarta');

        // 1. Siapkan Akun Kasir Demo 3 Usaha
        $kasirBarber = User::updateOrCreate(
            ['email' => 'kasir@simaul.com'],
            ['name' => 'Kasir Barbershop', 'phone' => '081290001001', 'password' => $passwordHash, 'role_id' => $cashierRole->id]
        );
        $kasirBarber->branches()->syncWithoutDetaching([1]);

        $kasirLaundry = User::updateOrCreate(
            ['email' => 'kasir.laundry@simaul.com'],
            ['name' => 'Kasir Laundry', 'phone' => '081290001002', 'password' => $passwordHash, 'role_id' => $cashierRole->id]
        );
        $kasirLaundry->branches()->syncWithoutDetaching([2]);

        $kasirAuto = User::updateOrCreate(
            ['email' => 'kasir.autocare@simaul.com'],
            ['name' => 'Kasir Auto Care', 'phone' => '081290001003', 'password' => $passwordHash, 'role_id' => $cashierRole->id]
        );
        $kasirAuto->branches()->syncWithoutDetaching([3]);

        // 2. Siapkan Fixture Pelanggan Pengujian Demo
        $pelanggan1 = User::updateOrCreate(
            ['email' => 'pelanggan.barber@simaul.com'],
            ['name' => 'Rudi Barbershop Demo', 'phone' => '081298881001', 'password' => $passwordHash, 'role_id' => $customerRole->id]
        );
        $pelanggan2 = User::updateOrCreate(
            ['email' => 'pelanggan.laundry@simaul.com'],
            ['name' => 'Siti Laundry Demo', 'phone' => '081298881002', 'password' => $passwordHash, 'role_id' => $customerRole->id]
        );
        $pelanggan3 = User::updateOrCreate(
            ['email' => 'pelanggan.autocare@simaul.com'],
            ['name' => 'Bayu Auto Care Demo', 'phone' => '081298881003', 'password' => $passwordHash, 'role_id' => $customerRole->id]
        );

        // Pelanggan untuk uji pendaftaran membership
        $custMember = User::updateOrCreate(
            ['email' => 'pelanggan.member@simaul.com'],
            ['name' => 'Hendra Member Demo', 'phone' => '081298882001', 'password' => $passwordHash, 'role_id' => $customerRole->id]
        );
        if (! $custMember->membership) {
            app(MemberService::class)->createMember($kasirBarber, [
                'user_id' => $custMember->id,
                'member_code' => 'MBR-DEMO99',
            ]);
        }

        User::updateOrCreate(
            ['email' => 'pelanggan.nonmember@simaul.com'],
            ['name' => 'Dewi NonMember Demo', 'phone' => '081298882002', 'password' => $passwordHash, 'role_id' => $customerRole->id]
        );

        // 3. Siapkan Shift Kasir Demo (Open Shift hari ini)
        $shiftBarber = CashierShift::firstOrCreate(
            ['user_id' => $kasirBarber->id, 'branch_id' => 1, 'status' => CashierShiftStatus::Open],
            ['opened_at' => $now->copy()->subHours(2), 'opening_cash' => 200000]
        );

        $shiftLaundry = CashierShift::firstOrCreate(
            ['user_id' => $kasirLaundry->id, 'branch_id' => 2, 'status' => CashierShiftStatus::Open],
            ['opened_at' => $now->copy()->subHours(3), 'opening_cash' => 250000]
        );

        $shiftAuto = CashierShift::firstOrCreate(
            ['user_id' => $kasirAuto->id, 'branch_id' => 3, 'status' => CashierShiftStatus::Open],
            ['opened_at' => $now->copy()->subHours(4), 'opening_cash' => 300000]
        );

        // 4. Barbershop & Studio: Booking Siap Bayar & Transaksi Walk-In
        $svcBarber = Service::where('branch_id', 1)->first() ?? Service::find(1);
        $bookingBarber = Booking::firstOrCreate(
            ['user_id' => $pelanggan1->id, 'branch_id' => 1, 'notes' => 'Booking demo untuk finalisasi pembayaran kasir'],
            [
                'service_id' => $svcBarber->id,
                'booking_date' => $now->copy()->addDay()->toDateString(),
                'booking_time' => '10:00:00',
                'status' => BookingStatus::Confirmed,
            ]
        );

        $bookingPaidBarber = Booking::firstOrCreate(
            ['user_id' => $pelanggan1->id, 'branch_id' => 1, 'notes' => 'Booking selesai Barbershop'],
            [
                'service_id' => $svcBarber->id,
                'booking_date' => $now->copy()->subDays(1)->toDateString(),
                'booking_time' => '09:00:00',
                'status' => BookingStatus::Completed,
            ]
        );

        $trxBarber = Transaction::firstOrCreate(
            ['transaction_code' => 'TRX-DEMO-BARBER-01'],
            [
                'branch_id' => 1,
                'cashier_id' => $kasirBarber->id,
                'booking_id' => $bookingPaidBarber->id,
                'subtotal' => 95000,
                'discount' => 15000,
                'total' => 80000,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => $now->copy()->subHour(),
            ]
        );
        TransactionDetail::firstOrCreate(
            ['transaction_id' => $trxBarber->id, 'service_id' => $svcBarber->id],
            ['qty' => 1, 'price' => 95000, 'subtotal' => 95000]
        );
        Payment::firstOrCreate(
            ['transaction_id' => $trxBarber->id],
            ['payment_method' => 'cash', 'amount' => 80000, 'status' => StatusPembayaran::Completed, 'paid_at' => $now->copy()->subHour()]
        );

        // 5. Laundry & Shoe Care: Order Siap Bayar & Transaksi Walk-In
        $svcLaundry = Service::where('branch_id', 2)->first() ?? Service::find(4);
        $orderLaundry = Order::firstOrCreate(
            ['user_id' => $pelanggan2->id, 'branch_id' => 2, 'service_id' => $svcLaundry->id, 'status' => StatusPesanan::Received],
            ['estimated_quantity' => 3, 'estimated_finish_at' => $now->copy()->addDays(2), 'notes' => 'Pesanan drop-off demo kasir']
        );

        $trxLaundry = Transaction::firstOrCreate(
            ['transaction_code' => 'TRX-DEMO-LAUNDRY-01'],
            [
                'branch_id' => 2,
                'cashier_id' => $kasirLaundry->id,
                'subtotal' => 65000,
                'discount' => 6500,
                'total' => 58500,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => $now->copy()->subHours(2),
            ]
        );
        TransactionDetail::firstOrCreate(
            ['transaction_id' => $trxLaundry->id, 'service_id' => $svcLaundry->id],
            ['qty' => 1, 'price' => 65000, 'subtotal' => 65000]
        );
        Payment::firstOrCreate(
            ['transaction_id' => $trxLaundry->id],
            ['payment_method' => 'qris', 'amount' => 58500, 'status' => StatusPembayaran::Completed, 'paid_at' => $now->copy()->subHours(2)]
        );

        // 6. Auto Care & Detailing: Booking & Order Siap Bayar + Transaksi Walk-in
        $svcAutoAppt = Service::where('branch_id', 3)->where('service_type', 'appointment')->first() ?? Service::find(9);
        $svcAutoDrop = Service::where('branch_id', 3)->where('service_type', 'drop_off')->first() ?? Service::find(8);

        Booking::firstOrCreate(
            ['user_id' => $pelanggan3->id, 'branch_id' => 3, 'notes' => 'Booking detailing demo'],
            [
                'service_id' => $svcAutoAppt->id,
                'booking_date' => $now->copy()->addDays(2)->toDateString(),
                'booking_time' => '13:00:00',
                'status' => BookingStatus::Confirmed,
            ]
        );

        Order::firstOrCreate(
            ['user_id' => $pelanggan3->id, 'branch_id' => 3, 'service_id' => $svcAutoDrop->id, 'status' => StatusPesanan::Received],
            ['estimated_quantity' => 1, 'estimated_finish_at' => $now->copy()->addDay(), 'notes' => 'Pesanan cuci mobil demo']
        );

        $trxAuto = Transaction::firstOrCreate(
            ['transaction_code' => 'TRX-DEMO-AUTOCARE-01'],
            [
                'branch_id' => 3,
                'cashier_id' => $kasirAuto->id,
                'subtotal' => 90000,
                'discount' => 18000,
                'total' => 72000,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => $now->copy()->subHour(),
            ]
        );
        TransactionDetail::firstOrCreate(
            ['transaction_id' => $trxAuto->id, 'service_id' => $svcAutoDrop->id],
            ['qty' => 1, 'price' => 90000, 'subtotal' => 90000]
        );
        Payment::firstOrCreate(
            ['transaction_id' => $trxAuto->id],
            ['payment_method' => 'cash', 'amount' => 72000, 'status' => StatusPembayaran::Completed, 'paid_at' => $now->copy()->subHour()]
        );
    }
}
