<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAvatarSeeder extends Seeder
{
    /**
     * Hubungkan foto profil manusia realistis ke seluruh akun demo SIMAUL.
     * Idempoten dan aman untuk database lokal/testing.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $avatarMap = [
            'owner@example.com' => 'images/avatars/owner.jpg',
            'manager@example.com' => 'images/avatars/manager-barber.jpg',
            'manager.selatan@example.com' => 'images/avatars/manager-laundry.jpg',
            'manager.barat@example.com' => 'images/avatars/manager-auto.jpg',
            'cashier@example.com' => 'images/avatars/cashier-barber.jpg',
            'kasir@simaul.com' => 'images/avatars/cashier-barber.jpg',
            'cashier.selatan@example.com' => 'images/avatars/cashier-laundry.jpg',
            'kasir.laundry@simaul.com' => 'images/avatars/cashier-laundry.jpg',
            'cashier.barat@example.com' => 'images/avatars/cashier-auto.jpg',
            'kasir.autocare@simaul.com' => 'images/avatars/cashier-auto.jpg',
            'karyawan@simaul.com' => 'images/avatars/employee-barber.jpg',
            'karyawan.laundry@simaul.com' => 'images/avatars/employee-laundry.jpg',
            'karyawan.autocare@simaul.com' => 'images/avatars/employee-auto.jpg',
            'pelanggan.barber@simaul.com' => 'images/avatars/customer-rudi.jpg',
            'customer@example.com' => 'images/avatars/customer-rudi.jpg',
            'pelanggan.laundry@simaul.com' => 'images/avatars/customer-siti.jpg',
            'annisa@example.com' => 'images/avatars/customer-siti.jpg',
            'pelanggan.autocare@simaul.com' => 'images/avatars/customer-rudi.jpg',
            'pelanggan.member@simaul.com' => 'images/avatars/customer-rudi.jpg',
            'pelanggan.nonmember@simaul.com' => 'images/avatars/customer-siti.jpg',
            'andi@simaul.com' => 'images/avatars/customer-rudi.jpg',
            'budi@simaul.com' => 'images/avatars/customer-rudi.jpg',
        ];

        foreach ($avatarMap as $email => $path) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['avatar' => $path]);
            }
        }

        $employeeMap = [
            'Dedi Kurniawan' => 'images/avatars/employee-barber.jpg',
            'Bayu Pratama' => 'images/avatars/employee-barber.jpg',
            'Reza Gunawan' => 'images/avatars/employee-barber.jpg',
            'Gilang Ramadhan' => 'images/avatars/employee-barber.jpg',
            'Agus Santoso' => 'images/avatars/employee-laundry.jpg',
            'Siti Aisyah' => 'images/avatars/customer-siti.jpg',
            'Budi Hartono' => 'images/avatars/employee-laundry.jpg',
            'Maya Indah' => 'images/avatars/customer-siti.jpg',
            'Fajar Hidayat' => 'images/avatars/employee-auto.jpg',
            'Eko Prasetyo' => 'images/avatars/employee-auto.jpg',
            'Wahyu Hidayat' => 'images/avatars/employee-auto.jpg',
            'Doni Kurnia' => 'images/avatars/employee-auto.jpg',
        ];

        foreach ($employeeMap as $name => $path) {
            Employee::where('name', $name)->update(['avatar' => $path]);
        }
    }
}
