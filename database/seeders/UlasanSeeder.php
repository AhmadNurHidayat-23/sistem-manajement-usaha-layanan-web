<?php

namespace Database\Seeders;

use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UlasanSeeder extends Seeder
{
    /**
     * Jalankan seeder ulasan dan rating pelanggan untuk layanan cabang.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->first() ?? Branch::firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->first() ?? Branch::firstOrFail();
        $cashier = User::where('email', 'cashier@example.com')->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $employeeA = Employee::where('branch_id', $branchA->id)->first();

        // Customer 1 (Existing)
        $customer1 = User::where('email', 'customer@example.com')->firstOrFail();
        $service1 = Service::where('name', 'Potong Rambut Pria')->first() ?? Service::firstOrFail();
        $transaction1 = Transaction::where('transaction_code', 'TRX-DEMO-001')->firstOrFail();

        // 1. Ulasan Pelanggan 1 (Cabang A - Pusat)
        Ulasan::firstOrCreate(
            ['transaction_id' => $transaction1->id],
            [
                'user_id' => $customer1->id,
                'service_id' => $service1->id,
                'employee_id' => $employeeA?->id,
                'rating' => 5,
                'komentar' => 'Pelayanan barbershop luar biasa memuaskan! Mas Dedi sangat teliti, hasil potongan fade rapi, dan suasana studio nyaman.',
            ]
        );

        // Customer 2
        $customer2 = User::firstOrCreate(
            ['email' => 'annisa@example.com'],
            [
                'name' => 'Annisa Larasati',
                'phone' => '081298765432',
                'password' => Hash::make('password123'),
                'role_id' => $customerRole?->id,
            ]
        );
        $service2 = Service::where('name', 'like', '%Jas%')->orWhere('name', 'like', '%Sepatu%')->first()
            ?? Service::where('id', '!=', $service1->id)->first()
            ?? $service1;

        $transaction2 = Transaction::firstOrCreate(
            ['transaction_code' => 'TRX-DEMO-002'],
            [
                'branch_id' => $branchB->id,
                'cashier_id' => $cashier?->id ?? 1,
                'subtotal' => 120000,
                'discount' => 0,
                'total' => 120000,
                'status' => StatusTransaksi::Paid,
                'transaction_date' => Carbon::now()->subDays(2),
            ]
        );

        // 2. Ulasan Pelanggan 2 (Cabang B - Selatan)
        Ulasan::firstOrCreate(
            ['transaction_id' => $transaction2->id],
            [
                'user_id' => $customer2->id,
                'service_id' => $service2->id,
                'employee_id' => null,
                'rating' => 5,
                'komentar' => 'Sepatu sneakers dan jas kerja saya ditangani sangat profesional. Bersih tuntas, wangi tahan lama, dan tidak ada kerutan.',
            ]
        );
    }
}
