<?php

use App\Console\Commands\KirimPengingatBooking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat booking via WhatsApp
Schedule::command(KirimPengingatBooking::class)->dailyAt('08:00');
