<?php

namespace App\Services\DataDemo;

use App\Models\Aktivitas;
use App\Models\User;
use App\Services\AktivitasService;

class AktivitasDemoService
{
    public function __construct(
        protected AktivitasService $aktivitasService
    ) {}

    /**
     * Catat aktivitas audit log demo sistem.
     *
     * @param  (callable(string, string): void)|null  $logger
     */
    public function jalankan(?callable $logger = null): void
    {
        $owner = User::whereIn('email', ['owner@simaul.com', 'owner@example.com'])->first();
        $managerA = User::whereIn('email', ['manajer@simaul.com', 'manager@example.com'])->first();
        $managerB = User::whereIn('email', ['manajer.laundry@simaul.com', 'manager.selatan@example.com'])->first();
        $managerC = User::whereIn('email', ['manajer.autocare@simaul.com', 'manager.barat@example.com'])->first();
        $cashierA = User::whereIn('email', ['kasir@simaul.com', 'kasir.andi@simaul.com', 'cashier@example.com'])->first();

        $activityDefs = [
            ['user' => $owner, 'act' => 'Owner meninjau laporan konsolidasi seluruh cabang', 'mod' => 'LaporanKeuangan', 'ket' => 'Melihat grafik omzet dan performa cabang'],
            ['user' => $managerA, 'act' => 'Manager Cabang Pusat memeriksa ketersediaan slot antrian', 'mod' => 'BookingAntrian', 'ket' => 'Verifikasi jadwal hari ini'],
            ['user' => $managerB, 'act' => 'Manager Cabang Selatan mengecek rak pengambilan pakaian', 'mod' => 'PesananDropOff', 'ket' => 'Memastikan status ready telah terupdate'],
            ['user' => $managerC, 'act' => 'Manager Cabang Barat mengontrol penggunaan coating nanoceramic', 'mod' => 'Inventori', 'ket' => 'Stok liquid aman'],
            ['user' => $cashierA, 'act' => 'Kasir membuka shift harian dengan modal awal', 'mod' => 'KasirPOS', 'ket' => 'Modal Rp 200.000'],
        ];

        foreach ($activityDefs as $def) {
            if (! $def['user']) {
                continue;
            }

            $exists = Aktivitas::where('user_id', $def['user']->id)
                ->where('aktivitas', $def['act'])
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                $this->aktivitasService->catat(
                    $def['user'],
                    $def['act'],
                    $def['mod'],
                    $def['ket']
                );
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Aktivitas ({$def['act']}) dilewati: {$e->getMessage()}");
                }
            }
        }
    }
}
