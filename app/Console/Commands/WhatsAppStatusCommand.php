<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'whatsapp:status';

    /**
     * The console command description.
     */
    protected $description = 'Periksa status koneksi WhatsApp Gateway SIMAUL Web';

    public function __construct(protected WhatsAppService $whatsApp)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=============================================');
        $this->info('  SIMAUL WEB — Status Integrasi WhatsApp     ');
        $this->info('=============================================');

        $driver = $this->whatsApp->getDriver();
        $testNumber = config('services.whatsapp.test_number');
        $isQueue = config('services.whatsapp.queue', false);

        $status = $this->whatsApp->checkConnection();

        $rows = [
            ['Driver WhatsApp', strtoupper($driver)],
            ['Status Koneksi', $status['connected'] ? '🟢 Terhubung (Connected)' : ($status['status'] === 'offline' ? '🔴 Offline' : '🟡 Belum Terhubung (Disconnected)')],
            ['Pesan Status', $status['message']],
            ['Mode Antrean (Queue)', $isQueue ? 'Aktif (Kirim via background job)' : 'Non-aktif (Kirim langsung)'],
            ['Nomor Pengujian (Test)', $testNumber ? $testNumber.' [Semua pesan diarahkan ke sini]' : 'Tidak diset (Mengirim ke nomor penerima asli)'],
        ];

        if ($driver === 'baileys') {
            $rows[] = ['Gateway URL', config('services.whatsapp.baileys_url', 'http://127.0.0.1:3001')];
            if (isset($status['qr_url'])) {
                $rows[] = ['QR Code Web URL', $status['qr_url']];
            }
        }

        $this->table(['Parameter', 'Nilai'], $rows);

        if (! $status['connected'] && ($status['has_qr'] ?? false)) {
            $this->newLine();
            $this->warn('⚠️ PERANGKAT WHATSAPP BELUM TAUT:');
            $this->line('1. Buka browser dan akses halaman QR code:');
            $this->comment('   '.($status['qr_url'] ?? 'http://127.0.0.1:3001/qr'));
            $this->line('2. Buka aplikasi WhatsApp di HP Anda.');
            $this->line('3. Buka menu Titik Tiga (Android) atau Pengaturan (iOS) > Perangkat Tertaut (Linked Devices).');
            $this->line('4. Pilih "Tautkan Perangkat" dan arahkan kamera ke kode QR.');
            $this->line('5. Setelah berhasil, jalankan kembali: php artisan whatsapp:status');
        } elseif (! $status['connected'] && $status['status'] === 'offline' && $driver === 'baileys') {
            $this->newLine();
            $this->error('⚠️ GATEWAY BAILEYS OFFLINE:');
            $this->line('Pastikan server microservice Baileys berjalan dengan perintah:');
            $this->comment('   node whatsapp-gateway/src/index.js');
        }

        $this->newLine();

        return $status['connected'] ? self::SUCCESS : self::FAILURE;
    }
}
