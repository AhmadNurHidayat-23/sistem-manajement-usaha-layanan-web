<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    /**
     * Jalankan seeder akun demo pengguna semua role untuk 3 cabang.
     */
    public function run(): void
    {
        $demoPassword = Hash::make('password123');

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();

        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        // 1. Akun Owner (Akses penuh ke seluruh 3 cabang)
        $owner = User::updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Budi Hartono',
                'phone' => '08110000001',
                'password' => $demoPassword,
                'role_id' => $ownerRole->id,
            ]
        );
        $owner->branches()->syncWithoutDetaching([$branchA->id, $branchB->id, $branchC->id]);

        // 2. Akun Manager Cabang A (Pusat - Barbershop & Studio)
        $managerA = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Siti Rahma',
                'phone' => '08110000002',
                'password' => $demoPassword,
                'role_id' => $managerRole->id,
            ]
        );
        $managerA->branches()->sync([$branchA->id]);

        // 3. Akun Manager Cabang B (Selatan - Laundry & Shoes)
        $managerB = User::updateOrCreate(
            ['email' => 'manager.selatan@example.com'],
            [
                'name' => 'Hendro Wijaya',
                'phone' => '08110000012',
                'password' => $demoPassword,
                'role_id' => $managerRole->id,
            ]
        );
        $managerB->branches()->sync([$branchB->id]);

        // 4. Akun Manager Cabang C (Barat - Auto Care & Detailing)
        $managerC = User::updateOrCreate(
            ['email' => 'manager.barat@example.com'],
            [
                'name' => 'Ratna Kusuma',
                'phone' => '08110000013',
                'password' => $demoPassword,
                'role_id' => $managerRole->id,
            ]
        );
        $managerC->branches()->sync([$branchC->id]);

        // 5. Akun Kasir Cabang A (Pusat)
        $cashierA = User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Andi Pratama',
                'phone' => '08110000003',
                'password' => $demoPassword,
                'role_id' => $cashierRole->id,
            ]
        );
        $cashierA->branches()->sync([$branchA->id]);

        // Kasir Cabang B (Selatan)
        $cashierB = User::updateOrCreate(
            ['email' => 'cashier.selatan@example.com'],
            [
                'name' => 'Dewi Sartika',
                'phone' => '08110000014',
                'password' => $demoPassword,
                'role_id' => $cashierRole->id,
            ]
        );
        $cashierB->branches()->sync([$branchB->id]);

        // Kasir Cabang C (Barat)
        $cashierC = User::updateOrCreate(
            ['email' => 'cashier.barat@example.com'],
            [
                'name' => 'Bagus Wicaksono',
                'phone' => '08110000015',
                'password' => $demoPassword,
                'role_id' => $cashierRole->id,
            ]
        );
        $cashierC->branches()->sync([$branchC->id]);

        // 6. Akun Karyawan Cabang A (Barbershop - Stylist)
        $employeeAUser = User::where('email', 'employee@example.com')->first();
        if ($employeeAUser) {
            $employeeAUser->update(['email' => 'karyawan@simaul.com']);
        } else {
            $employeeAUser = User::updateOrCreate(
                ['email' => 'karyawan@simaul.com'],
                [
                    'name' => 'Dedi Kurniawan',
                    'phone' => '08110000004',
                    'password' => $demoPassword,
                    'role_id' => $employeeRole->id,
                ]
            );
        }
        $employeeAUser->branches()->sync([$branchA->id]);

        // Karyawan Cabang B (Laundry - Operator Laundry)
        $employeeBUser = User::where('email', 'employee.selatan@example.com')->first();
        if ($employeeBUser) {
            $employeeBUser->update(['email' => 'karyawan.laundry@simaul.com']);
        } else {
            $employeeBUser = User::updateOrCreate(
                ['email' => 'karyawan.laundry@simaul.com'],
                [
                    'name' => 'Agus Santoso',
                    'phone' => '08110000005',
                    'password' => $demoPassword,
                    'role_id' => $employeeRole->id,
                ]
            );
        }
        $employeeBUser->branches()->sync([$branchB->id]);

        // Karyawan Cabang C (Auto Care - Detailer)
        $employeeCUser = User::where('email', 'employee.barat@example.com')->first();
        if ($employeeCUser) {
            $employeeCUser->update(['email' => 'karyawan.autocare@simaul.com']);
        } else {
            $employeeCUser = User::updateOrCreate(
                ['email' => 'karyawan.autocare@simaul.com'],
                [
                    'name' => 'Fajar Hidayat',
                    'phone' => '08110000006',
                    'password' => $demoPassword,
                    'role_id' => $employeeRole->id,
                ]
            );
        }
        $employeeCUser->branches()->sync([$branchC->id]);

        // 7. Akun Pelanggan Demo Awal
        $customer = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Rian Saputra',
                'phone' => '081234567890',
                'password' => $demoPassword,
                'role_id' => $customerRole->id,
            ]
        );
        $customer->branches()->syncWithoutDetaching([$branchA->id, $branchB->id, $branchC->id]);
    }
}
