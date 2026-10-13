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
        $owner = User::where('email', 'owner@example.com')->first();
        $managerA = User::where('email', 'manager@example.com')->first();
        $managerB = User::where('email', 'manager.selatan@example.com')->first();
        $managerC = User::where('email', 'manager.barat@example.com')->first();
        $cashierA = User::where('email', 'cashier@example.com')->first();

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
