<?php

namespace Database\Seeders;

use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCustomerSeeder extends Seeder
{
    /**
     * Seed akun demo pelanggan Andi (order Processing) dan Budi (order Completed tanpa ulasan).
     */
    public function run(): void
    {
        $password = Hash::make('password123');
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $branchSouth = Branch::where('name', 'like', '%Selatan%')->first()
            ?? Branch::firstOrCreate(
                ['name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)'],
                [
                    'address' => 'Jl. RS Fatmawati No. 45, Jakarta Selatan',
                    'service_mode' => 'drop_off',
                    'opening_time' => '08:00',
                    'closing_time' => '20:00',
                    'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
                    'max_slot_capacity' => 8,
                    'is_active' => true,
                ]
            );

        $serviceLaundry = Service::where('branch_id', $branchSouth->id)->first()
            ?? Service::firstOrCreate(
                ['name' => 'Cuci Kering Setrika', 'branch_id' => $branchSouth->id],
                [
                    'price' => 35000,
                    'service_type' => 'drop_off',
                    'unit' => 'kg',
                    'estimated_duration' => 120,
                    'is_active' => true,
                ]
            );

        // 1. Akun Andi (Status Pesanan: Processing)
        $andi = User::updateOrCreate(
            ['email' => 'andi@simaul.com'],
            [
                'name' => 'Andi Pratama',
                'phone' => '081234567891',
                'password' => $password,
                'role_id' => $customerRole->id,
            ]
        );

        $andiOrder = Order::updateOrCreate(
            [
                'user_id' => $andi->id,
                'branch_id' => $branchSouth->id,
                'notes' => 'Pesanan Demo Andi — Status Processing',
            ],
            [
                'service_id' => $serviceLaundry->id,
                'estimated_quantity' => 3.50,
                'pickup_option' => false,
                'estimated_finish_at' => now()->addHours(4),
                'status' => StatusPesanan::Processing,
            ]
        );

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $cashier = User::where('role_id', $cashierRole?->id ?? 3)->first() ?? $andi;

        $andiTrx = Transaction::updateOrCreate(
            ['order_id' => $andiOrder->id],
            [
                'branch_id' => $branchSouth->id,
                'cashier_id' => $cashier->id,
                'transaction_code' => 'TRX-ANDI-'.$andiOrder->id,
                'subtotal' => 35000,
                'discount' => 0,
                'total' => 35000,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => now()->subHours(1),
            ]
        );

        // Pastikan Andi tidak memiliki ulasan
        Ulasan::where('user_id', $andi->id)->delete();
        Ulasan::where('transaction_id', $andiTrx->id)->delete();

        // 2. Akun Budi (Status Pesanan: Completed, Ulasan KOSONG)
        $budi = User::updateOrCreate(
            ['email' => 'budi@simaul.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567892',
                'password' => $password,
                'role_id' => $customerRole->id,
            ]
        );

        $budiOrder = Order::updateOrCreate(
            [
                'user_id' => $budi->id,
                'branch_id' => $branchSouth->id,
                'notes' => 'Pesanan Demo Budi — Status Completed (Siap Diulas)',
            ],
            [
                'service_id' => $serviceLaundry->id,
                'estimated_quantity' => 5.00,
                'pickup_option' => true,
                'estimated_finish_at' => now()->subHours(1),
                'status' => StatusPesanan::Completed,
            ]
        );

        $budiTrx = Transaction::updateOrCreate(
            ['order_id' => $budiOrder->id],
            [
                'branch_id' => $branchSouth->id,
                'cashier_id' => $cashier->id,
                'transaction_code' => 'TRX-BUDI-'.$budiOrder->id,
                'subtotal' => 50000,
                'discount' => 0,
                'total' => 50000,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => now()->subHours(2),
            ]
        );

        // Pastikan ulasan Budi kosong untuk pengujian manual mandiri
        Ulasan::where('user_id', $budi->id)->delete();
        Ulasan::where('transaction_id', $budiTrx->id)->delete();
    }
}
