<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    /**
     * Jalankan seeder riwayat log aktivitas pengguna.
     */
    public function run(): void
    {
        $owner = User::whereIn('email', ['owner@simaul.com', 'owner@example.com'])->firstOrFail();
        $manager = User::whereIn('email', ['manajer@simaul.com', 'manager@example.com'])->firstOrFail();
        $cashier = User::whereIn('email', ['kasir@simaul.com', 'kasir.andi@simaul.com', 'cashier@example.com'])->firstOrFail();

        // 1. Aktivitas Owner (Tambah Cabang)
        Aktivitas::firstOrCreate(
            ['keterangan' => 'Cabang SIMAUL Barbershop & Studio (Cabang Pusat) ditambahkan ke sistem'],
            [
                'user_id' => $owner->id,
                'aktivitas' => 'Tambah Cabang',
                'modul' => 'cabang',
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::now()->subDays(5),
            ]
        );

        // 2. Aktivitas Manager (Tambah Inventori)
        Aktivitas::firstOrCreate(
            ['keterangan' => 'Bahan baru Pomade Matte Clay Premium 100g ditambahkan'],
            [
                'user_id' => $manager->id,
                'aktivitas' => 'Tambah Inventori',
                'modul' => 'inventori',
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        // 3. Aktivitas Kasir (Buat Transaksi)
        Aktivitas::firstOrCreate(
            ['keterangan' => "Transaksi 'TRX-DEMO-001' total Rp 85.500 diproses."],
            [
                'user_id' => $cashier->id,
                'aktivitas' => 'Buat Transaksi',
                'modul' => 'transaksi',
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::yesterday()->setTime(11, 50),
            ]
        );
    }
}
