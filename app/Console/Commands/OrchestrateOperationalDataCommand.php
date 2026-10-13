<?php

namespace App\Console\Commands;

use App\Services\DataDemo\AktivitasDemoService;
use App\Services\DataDemo\BookingDemoService;
use App\Services\DataDemo\InventoriDemoService;
use App\Services\DataDemo\PelangganDemoService;
use App\Services\DataDemo\PesananDemoService;
use App\Services\DataDemo\TransaksiDemoService;
use App\Services\DataDemo\UlasanDemoService;
use Illuminate\Console\Command;

class OrchestrateOperationalDataCommand extends Command
{
    protected $signature = 'app:orchestrate-operational-data';

    protected $description = 'Orkestrasi data operasional realistis SIMAUL melalui flow domain service resmi';

    public function handle(
        PelangganDemoService $pelangganDemoService,
        BookingDemoService $bookingDemoService,
        PesananDemoService $pesananDemoService,
        TransaksiDemoService $transaksiDemoService,
        InventoriDemoService $inventoriDemoService,
        UlasanDemoService $ulasanDemoService,
        AktivitasDemoService $aktivitasDemoService
    ): int {
        // Proteksi environment: data demo tidak boleh dijalankan di production
        if (app()->environment('production')) {
            $this->error('Data demo operasional tidak dapat dijalankan pada environment production.');

            return self::FAILURE;
        }

        $this->info('Memulai orkestrasi data operasional realistis SIMAUL...');

        $logger = function (string $level, string $msg): void {
            if ($level === 'warn') {
                $this->warn($msg);
            } elseif ($level === 'error') {
                $this->error($msg);
            } else {
                $this->line($msg);
            }
        };

        // 1. Pelanggan & Member
        $this->info('Menyiapkan pelanggan dan member realistis...');
        $customers = $pelangganDemoService->jalankan($logger);

        // 2. Booking Appointment (Cabang A & Cabang C)
        $this->info('Mengorkestrasi booking appointment (Cabang A & C)...');
        $bookingDemoService->jalankan($customers, $logger);

        // 3. Pesanan Drop-off (Cabang B & Cabang C)
        $this->info('Mengorkestrasi pesanan drop-off (Cabang B & C)...');
        $pesananDemoService->jalankan($customers, $logger);

        // 4. Shift Kasir, Transaksi POS & Pembayaran
        $this->info('Mengorkestrasi shift kasir dan transaksi POS...');
        $transaksiDemoService->jalankan($logger);

        // 5. Mutasi Stok Bahan Inventori
        $this->info('Mengorkestrasi mutasi inventori...');
        $inventoriDemoService->jalankan($logger);

        // 6. Ulasan / Review Pelanggan
        $this->info('Mengorkestrasi ulasan pelanggan realistis...');
        $ulasanDemoService->jalankan($logger);

        // 7. Jejak Aktivitas Sistem
        $this->info('Mencatat jejak aktivitas sistem...');
        $aktivitasDemoService->jalankan($logger);

        $this->newLine();
        $this->info('Orkestrasi data operasional SIMAUL selesai.');

        return self::SUCCESS;
    }
}
