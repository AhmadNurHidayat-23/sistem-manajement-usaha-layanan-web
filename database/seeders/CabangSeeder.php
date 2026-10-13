<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Jalankan seeder 3 cabang demo dengan mode operasional dan jenis layanan berbeda.
     */
    public function run(): void
    {
        // 1. Cabang A (Pusat): Barbershop / Salon — Mode Appointment
        Branch::updateOrCreate(
            ['name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)'],
            [
                'address' => 'Jl. M.H. Thamrin No. 10, Jakarta Pusat',
                'phone' => '021-3901122',
                'opening_time' => '09:00',
                'closing_time' => '21:00',
                'service_mode' => 'appointment',
                'operating_days' => ['selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
                'max_slot_capacity' => 4,
                'is_active' => true,
            ]
        );

        // 2. Cabang B (Selatan): Laundry / Cuci Sepatu — Mode Drop-off
        Branch::updateOrCreate(
            ['name' => 'SIMAUL Laundry & Shoe Care (Cabang Selatan)'],
            [
                'address' => 'Jl. RS Fatmawati No. 45, Jakarta Selatan',
                'phone' => '021-7503344',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
                'service_mode' => 'drop_off',
                'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
                'max_slot_capacity' => 8,
                'is_active' => true,
            ]
        );

        // 3. Cabang C (Barat): Auto Care & Detailing — Mode Appointment + Drop-off (Both)
        Branch::updateOrCreate(
            ['name' => 'SIMAUL Auto Care & Detailing (Cabang Barat)'],
            [
                'address' => 'Jl. Panjang No. 88, Kebon Jeruk, Jakarta Barat',
                'phone' => '021-5302211',
                'opening_time' => '08:00',
                'closing_time' => '21:00',
                'service_mode' => 'both',
                'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
                'max_slot_capacity' => 6,
                'is_active' => true,
            ]
        );
    }
}
