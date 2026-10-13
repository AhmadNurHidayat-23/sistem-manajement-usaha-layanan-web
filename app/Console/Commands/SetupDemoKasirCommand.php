<?php

namespace App\Console\Commands;

use Database\Seeders\DemoKasirSeeder;
use Illuminate\Console\Command;

class SetupDemoKasirCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simaul:setup-demo-kasir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Siapkan akun kasir demo 3 unit usaha dan data transaksi/shift development';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Perintah ini hanya dapat dijalankan pada environment local atau testing.');

            return self::FAILURE;
        }

        $this->info('Menyiapkan akun kasir demo dan fixture data transaksi SIMAUL...');

        $seeder = new DemoKasirSeeder;
        $seeder->run();

        $this->newLine();
        $this->info('Akun kasir demo berhasil disiapkan:');

        $this->table(
            ['Nama Kasir', 'Nama Usaha', 'Email', 'URL Login', 'URL Dashboard'],
            [
                ['Kasir Barbershop', 'Barbershop & Studio', 'kasir@simaul.com', url('/login'), url('/cashier/dashboard')],
                ['Kasir Laundry', 'Laundry & Shoe Care', 'kasir.laundry@simaul.com', url('/login'), url('/cashier/dashboard')],
                ['Kasir Auto Care', 'Auto Care & Detailing', 'kasir.autocare@simaul.com', url('/login'), url('/cashier/dashboard')],
            ]
        );

        $this->newLine();
        $this->line('<comment>Info Password Demo:</comment> Password dikonfigurasi melalui variabel environment <info>DEMO_CASHIER_PASSWORD</info> di file .env.');
        $this->line('Periksa nilai password dengan perintah: <info>php artisan tinker --execute "echo env(\'DEMO_CASHIER_PASSWORD\');"</info>');

        return self::SUCCESS;
    }
}
