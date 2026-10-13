<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Jalankan seluruh seeder demo terstruktur sesuai urutan ketergantungan data.
     */
    public function run(): void
    {
        $this->call([
            PeranSeeder::class,
            CabangSeeder::class,
            PenggunaSeeder::class,
            KaryawanSeeder::class,
            LayananSeeder::class,
            ServiceImageSeeder::class,
            BookingSeeder::class,
            PromoSeeder::class,
            TransaksiSeeder::class,
            MemberSeeder::class,
            InventoriSeeder::class,
            UlasanSeeder::class,
            AktivitasSeeder::class,
            DemoBranchCompletenessSeeder::class,
            DemoKasirSeeder::class,
            DemoManagerSeeder::class,
            DemoCustomerSeeder::class,
            DemoEmployeeShiftSeeder::class,
            DemoAvatarSeeder::class,
        ]);
    }
}
