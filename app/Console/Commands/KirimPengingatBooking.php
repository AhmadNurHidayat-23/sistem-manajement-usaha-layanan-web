<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Kirim pengingat booking via WhatsApp.
 *
 * Schedule in routes/console.php:
 *   Schedule::command('notifikasi:pengingat-booking')->dailyAt('08:00');
 */
class KirimPengingatBooking extends Command
{
    protected $signature = 'notifikasi:pengingat-booking
                            {--date= : Target booking date (Y-m-d). Defaults to tomorrow.}
                            {--dry-run : List bookings that would be notified without sending.}';

    protected $description = 'Kirim pengingat WhatsApp ke customer yang memiliki booking besok';

    public function __construct(protected NotifikasiService $notifikasiService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::tomorrow();

        $isDryRun = $this->option('dry-run');

        $this->info("📅 Mencari booking untuk tanggal: {$date->format('d M Y')}");

        $bookings = Booking::with(['user', 'service', 'branch'])
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('phone'))
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('Tidak ada booking yang memerlukan pengingat.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$bookings->count()} booking.");
        $this->newLine();

        $sent = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            $label = "Booking #{$booking->id} — {$booking->user->name} ({$booking->user->phone})";

            if ($isDryRun) {
                $this->line("[DRY-RUN] {$label}");

                continue;
            }

            try {
                $log = $this->notifikasiService->pengingatBooking($booking);

                if ($log?->isDelivered()) {
                    $this->line("✅ {$label}");
                    $sent++;
                } else {
                    $this->warn("⚠ {$label} — gagal: ".($log?->error ?? 'unknown'));
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("❌ {$label} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$sent} | Gagal: {$failed}");

        return self::SUCCESS;
    }
}
