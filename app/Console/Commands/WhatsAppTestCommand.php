<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'whatsapp:test
                            {phone? : Nomor tujuan WhatsApp (contoh: 081234567890)}
                            {message? : Pesan uji coba yang ingin dikirimkan}';

    /**
     * The console command description.
     */
    protected $description = 'Kirim pesan uji coba WhatsApp untuk memverifikasi gateway';

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
        $this->info('  SIMAUL WEB — Uji Coba Pengiriman WhatsApp  ');
        $this->info('=============================================');

        $driver = $this->whatsApp->getDriver();
        $this->line("Driver aktif: <comment>{$driver}</comment>");

        $phone = $this->argument('phone')
            ?: config('services.whatsapp.test_number')
            ?: $this->ask('Masukkan nomor WhatsApp tujuan uji coba (contoh: 081234567890)');

        if (! $phone) {
            $this->error('Nomor telepon tujuan tidak boleh kosong.');

            return self::FAILURE;
        }

        $defaultMessage = "*[SIMAUL WEB - UJI COBA KONEKSI]*\n\n"
            .'Halo, ini adalah pesan pengujian integrasi WhatsApp SIMAUL Web.'."\n"
            .'Waktu uji: '.now()->translatedFormat('d F Y H:i:s')."\n"
            .'Driver: '.strtoupper($driver)."\n\n"
            .'Sistem manajemen usaha layanan Anda siap beroperasi!';

        $message = $this->argument('message') ?: $defaultMessage;

        $targetPhone = $this->whatsApp->resolveRecipientPhone($phone);

        $this->line('Nomor tujuan : '.WhatsAppService::maskPhone($targetPhone));
        $this->line('Mengirim pesan...');

        $result = $this->whatsApp->send($phone, $message);

        if ($result['success']) {
            $this->info(' Pengiriman berhasil!');
            $this->table(['Parameter', 'Detail'], [
                ['Status', 'BERHASIL (Success)'],
                ['Nomor Tujuan', WhatsAppService::maskPhone($targetPhone)],
                ['Reference ID', $result['reference'] ?? '-'],
                ['Response', is_string($result['raw']) ? $result['raw'] : json_encode($result['raw'])],
            ]);

            return self::SUCCESS;
        }

        $this->error(' Pengiriman gagal!');
        $this->table(['Parameter', 'Detail'], [
            ['Status', 'GAGAL (Failed)'],
            ['Nomor Tujuan', WhatsAppService::maskPhone($targetPhone)],
            ['Error Response', is_string($result['raw']) ? $result['raw'] : json_encode($result['raw'])],
        ]);

        if ($driver === 'baileys') {
            $this->newLine();
            $this->comment('Tips: Jika status "belum terhubung", buka http://127.0.0.1:3001/qr untuk scan QR WhatsApp.');
        }

        return self::FAILURE;
    }
}
