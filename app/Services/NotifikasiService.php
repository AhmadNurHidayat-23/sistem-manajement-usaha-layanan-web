<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Jobs\KirimNotifikasiWhatsAppJob;
use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Dispatcher notifikasi WhatsApp.
 */
class NotifikasiService
{
    public function __construct(protected WhatsAppService $whatsApp) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Transaction
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kirim struk transaksi via WhatsApp.
     */
    public function kirimStrukTransaksi(Transaction $transaction, ?string $overridePhone = null): ?NotificationLog
    {
        $transaction->loadMissing(['branch', 'details.service', 'payments', 'booking.user', 'order.user']);

        $user = $transaction->booking?->user ?? $transaction->order?->user;
        $rawPhone = $overridePhone ?: ($user?->phone ?? $transaction->customer_phone);

        if (! $rawPhone) {
            return null;
        }

        $phone = WhatsAppService::normalizeIndonesianPhone($rawPhone);
        if (! $phone || ! WhatsAppService::isValidIndonesianPhone($phone)) {
            return null;
        }

        $items = $transaction->details->map(function ($d) {
            $qty = (int) $d->qty === $d->qty ? (int) $d->qty : $d->qty;

            return "• {$d->service->name} (x{$qty}) : Rp ".number_format($d->subtotal, 0, ',', '.');
        })->implode("\n");

        $payment = $transaction->successfulPayment() ?? $transaction->payments->sortByDesc('id')->first();
        $paymentInfo = $payment
            ? strtoupper(str_replace('_', ' ', $payment->payment_method)).' ('.($payment->status?->label() ?? 'Sukses').')'
            : '—';

        $tanggal = Carbon::parse($transaction->transaction_date)->format('d M Y H:i');
        $diskonText = $transaction->discount > 0
            ? 'Diskon        : -Rp '.number_format($transaction->discount, 0, ',', '.')."\n"
            : '';

        $recipientName = $user?->name ?? $transaction->customer_name ?? 'Pelanggan';
        $greetingName = ($recipientName === 'Pelanggan Umum') ? 'Pelanggan' : $recipientName;

        $message = "*STRUK TRANSAKSI*\n\n"
            ."Halo {$greetingName},\n\n"
            ."Berikut adalah rincian transaksi pembayaran Anda.\n\n"
            ."No. Transaksi : {$transaction->transaction_code}\n"
            ."Tanggal       : {$tanggal}\n"
            ."Cabang        : {$transaction->branch->name}\n\n"
            ."Rincian Layanan:\n"
            ."{$items}\n\n"
            ."{$diskonText}"
            .'Total         : Rp '.number_format($transaction->total, 0, ',', '.')."\n"
            ."Pembayaran    : {$paymentInfo}\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $user,
            phone: $phone,
            type: 'transaction_receipt',
            message: $message,
            notifiable: $transaction,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Booking
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Notifikasi booking baru dibuat.
     */
    public function notifikasiBookingBaru(Booking $booking): ?NotificationLog
    {
        $booking->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $booking->user?->name ?? 'Pelanggan';
        $kodeBooking = $booking->booking_code;
        $tanggal = $this->formatTanggalIndo($booking->booking_date);
        $waktu = $this->formatWaktuIndo($booking->booking_time);
        $cabang = $booking->branch?->name ?? 'SIMAUL';
        $layanan = $this->resolveBookingServices($booking);

        $isConfirmed = $booking->status === BookingStatus::Confirmed;

        if ($isConfirmed) {
            $message = "*BOOKING TERKONFIRMASI*\n\n"
                ."Halo {$namaPelanggan},\n\n"
                ."Booking layanan Anda berhasil dibuat dan telah dikonfirmasi secara otomatis.\n\n"
                ."*Detail Booking*\n\n"
                ."Kode Booking : {$kodeBooking}\n"
                ."Tanggal : {$tanggal}\n"
                ."Waktu : {$waktu} WIB\n"
                ."Cabang : {$cabang}\n"
                ."Layanan : {$layanan}\n\n"
                ."Silakan datang sesuai jadwal booking yang telah ditentukan.\n\n"
                .'Terima kasih telah menggunakan SIMAUL.';
        } else {
            $message = "*BOOKING DIBUAT*\n\n"
                ."Halo {$namaPelanggan},\n\n"
                ."Booking layanan Anda telah kami terima dan saat ini menunggu konfirmasi.\n\n"
                ."*Detail Booking*\n\n"
                ."Kode Booking : {$kodeBooking}\n"
                ."Tanggal : {$tanggal}\n"
                ."Waktu : {$waktu} WIB\n"
                ."Cabang : {$cabang}\n"
                ."Layanan : {$layanan}\n"
                ."Status : Menunggu Konfirmasi\n\n"
                ."Kami akan menginformasikan kembali setelah booking dikonfirmasi.\n\n"
                .'Terima kasih telah menggunakan SIMAUL.';
        }

        return $this->kirim(
            user: $booking->user,
            phone: $phone,
            type: 'booking_created',
            message: $message,
            notifiable: $booking,
        );
    }

    /**
     * Notifikasi booking dikonfirmasi.
     */
    public function notifikasiBookingDikonfirmasi(Booking $booking): ?NotificationLog
    {
        $booking->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $booking->user?->name ?? 'Pelanggan';
        $kodeBooking = $booking->booking_code;
        $tanggal = $this->formatTanggalIndo($booking->booking_date);
        $waktu = $this->formatWaktuIndo($booking->booking_time);
        $cabang = $booking->branch?->name ?? 'SIMAUL';
        $layanan = $this->resolveBookingServices($booking);

        $message = "*BOOKING DIKONFIRMASI*\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."Booking Anda berhasil dikonfirmasi.\n\n"
            ."*Detail Booking*\n\n"
            ."Kode Booking : {$kodeBooking}\n"
            ."Tanggal : {$tanggal}\n"
            ."Waktu : {$waktu} WIB\n"
            ."Cabang : {$cabang}\n"
            ."Layanan : {$layanan}\n"
            ."Status : Terkonfirmasi\n\n"
            ."Silakan datang sesuai jadwal booking yang telah ditentukan.\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $booking->user,
            phone: $phone,
            type: 'booking_confirmed',
            message: $message,
            notifiable: $booking,
        );
    }

    /**
     * Notifikasi perubahan status booking.
     */
    public function notifikasiStatusBooking(Booking $booking): ?NotificationLog
    {
        $booking->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $booking->user?->name ?? 'Pelanggan';
        $kodeBooking = $booking->booking_code;
        $tanggal = $this->formatTanggalIndo($booking->booking_date);
        $waktu = $this->formatWaktuIndo($booking->booking_time);
        $cabang = $booking->branch?->name ?? 'SIMAUL';
        $layanan = $this->resolveBookingServices($booking);

        $statusValue = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) ($booking->status ?? '');

        $judul = match ($statusValue) {
            'confirmed' => '*BOOKING DIKONFIRMASI*',
            'in_progress' => '*BOOKING SEDANG DIKERJAKAN*',
            'completed' => '*BOOKING SELESAI*',
            'cancelled' => '*BOOKING DIBATALKAN*',
            default => '*UPDATE STATUS BOOKING*',
        };

        $statusLabel = match ($statusValue) {
            'confirmed' => 'Terkonfirmasi',
            'in_progress' => 'Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($statusValue),
        };

        $statusDesc = match ($statusValue) {
            'confirmed' => 'Booking Anda berhasil dikonfirmasi.',
            'in_progress' => 'Layanan booking Anda sedang dikerjakan.',
            'completed' => 'Layanan booking Anda telah selesai.',
            'cancelled' => 'Booking Anda telah dibatalkan.',
            default => 'Status booking Anda telah diperbarui.',
        };

        $instruksiLanjutan = $statusValue === 'confirmed'
            ? "Silakan datang sesuai jadwal booking yang telah ditentukan.\n\n"
            : '';

        $message = "{$judul}\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."{$statusDesc}\n\n"
            ."*Detail Booking*\n\n"
            ."Kode Booking : {$kodeBooking}\n"
            ."Tanggal : {$tanggal}\n"
            ."Waktu : {$waktu} WIB\n"
            ."Cabang : {$cabang}\n"
            ."Layanan : {$layanan}\n"
            ."Status : {$statusLabel}\n\n"
            ."{$instruksiLanjutan}"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $booking->user,
            phone: $phone,
            type: 'booking_status_changed',
            message: $message,
            notifiable: $booking,
        );
    }

    /**
     * Pengingat booking.
     */
    public function pengingatBooking(Booking $booking): ?NotificationLog
    {
        $booking->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $booking->user?->name ?? 'Pelanggan';
        $kodeBooking = $booking->booking_code;
        $tanggal = $this->formatTanggalIndo($booking->booking_date);
        $waktu = $this->formatWaktuIndo($booking->booking_time);
        $cabang = $booking->branch?->name ?? 'SIMAUL';
        $layanan = $this->resolveBookingServices($booking);

        $message = "*PENGINGAT JADWAL BOOKING*\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."Ini adalah pengingat untuk jadwal booking layanan Anda.\n\n"
            ."*Detail Booking*\n\n"
            ."Kode Booking : {$kodeBooking}\n"
            ."Tanggal : {$tanggal}\n"
            ."Waktu : {$waktu} WIB\n"
            ."Cabang : {$cabang}\n"
            ."Layanan : {$layanan}\n\n"
            ."Harap hadir tepat waktu sesuai jadwal yang telah ditentukan.\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $booking->user,
            phone: $phone,
            type: 'booking_reminder',
            message: $message,
            notifiable: $booking,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Order (drop-off/pick-up)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Notifikasi pesanan drop-off baru dibuat.
     */
    public function notifikasiOrderBaru(Order $order): ?NotificationLog
    {
        $order->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $order->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $order->user?->name ?? 'Pelanggan';
        $layanan = $this->resolveOrderServices($order);
        $cabang = $order->branch?->name ?? 'SIMAUL';
        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) ($order->status ?? '');
        $statusLabel = $statusValue === 'pending' ? 'Menunggu' : 'Diterima';
        $statusDesc = $statusValue === 'pending'
            ? 'Pesanan Anda telah tercatat dan sedang menunggu antrean layanan.'
            : 'Pesanan Anda telah diterima dan akan diproses sesuai antrean layanan.';

        $message = "*UPDATE STATUS PESANAN*\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."{$statusDesc}\n\n"
            ."*Detail Pesanan*\n\n"
            ."No. Pesanan : #{$order->id}\n"
            ."Layanan : {$layanan}\n"
            ."Cabang : {$cabang}\n"
            ."Status : {$statusLabel}\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $order->user,
            phone: $phone,
            type: 'order_created',
            message: $message,
            notifiable: $order,
        );
    }

    /**
     * Notifikasi rincian tagihan setelah penimbangan laundry kiloan dikonfirmasi kasir.
     */
    public function notifikasiTagihanLaundry(Order $order): ?NotificationLog
    {
        $order->loadMissing(['user', 'service', 'branch']);

        $phone = $order->user?->phone;
        if (! $phone) {
            return null;
        }

        $namaPelanggan = $order->user?->name ?? 'Pelanggan';
        $layanan = $order->service?->name ?? 'Laundry Kiloan';
        $berat = rtrim(rtrim(number_format((float) ($order->actual_weight ?? 0), 2, ',', '.'), '0'), ',');
        $hargaPerKg = 'Rp '.number_format((float) ($order->service?->price ?? 0), 0, ',', '.');
        $totalTagihan = 'Rp '.number_format((float) ($order->total_amount ?? $order->calculateBill()), 0, ',', '.');

        $message = "*RINCIAN TAGIHAN LAUNDRY*\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."Pesanan laundry Anda telah diterima dan selesai ditimbang.\n\n"
            ."No. Pesanan: #{$order->id}\n"
            ."Layanan: {$layanan}\n"
            ."Berat Aktual: {$berat} kg\n"
            ."Harga per Kg: {$hargaPerKg}\n"
            ."Total Tagihan: *{$totalTagihan}*\n\n"
            ."Pesanan Anda akan segera dikerjakan. Pembayaran dapat dilakukan setelah pekerjaan selesai.\n\n"
            .'Terima kasih telah menggunakan layanan kami.';

        return $this->kirim(
            user: $order->user,
            phone: $phone,
            type: "laundry_weighed_{$order->id}_{$order->actual_weight}",
            message: $message,
            notifiable: $order,
        );
    }

    /**
     * Notifikasi perubahan status pesanan.
     */
    public function notifikasiStatusOrder(Order $order): ?NotificationLog
    {
        $order->loadMissing(['user', 'service', 'branch', 'details.service']);

        $phone = $order->user?->phone;

        if (! $phone) {
            return null;
        }

        $namaPelanggan = $order->user?->name ?? 'Pelanggan';
        $layanan = $this->resolveOrderServices($order);
        $cabang = $order->branch?->name ?? 'SIMAUL';
        $isAntar = (bool) $order->pickup_option;

        $statusValue = $order->status instanceof \BackedEnum ? $order->status->value : (string) ($order->status ?? '');

        $statusLabel = match ($statusValue) {
            'pending' => 'Menunggu',
            'received' => 'Diterima',
            'processing' => 'Dikerjakan',
            'ready' => $isAntar ? 'Siap Diantar' : 'Siap Diambil',
            'completed' => $isAntar ? 'Telah Diantar' : 'Telah Diambil',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($statusValue),
        };

        $statusDesc = match ($statusValue) {
            'pending' => 'Pesanan Anda telah tercatat dan sedang menunggu antrean layanan.',
            'received' => 'Pesanan Anda telah diterima dan akan diproses sesuai antrean layanan.',
            'processing' => 'Pesanan Anda sedang dalam proses pengerjaan.',
            'ready' => $isAntar
                ? 'Pesanan Anda telah selesai dan siap diantar.'
                : 'Pesanan Anda telah selesai dan siap diambil.',
            'completed' => 'Pesanan Anda telah berhasil diserahkan.',
            'cancelled' => 'Pesanan Anda telah dibatalkan.',
            default => 'Status pesanan Anda telah diperbarui.',
        };

        $message = "*UPDATE STATUS PESANAN*\n\n"
            ."Halo {$namaPelanggan},\n\n"
            ."{$statusDesc}\n\n"
            ."*Detail Pesanan*\n\n"
            ."No. Pesanan : #{$order->id}\n"
            ."Layanan : {$layanan}\n"
            ."Cabang : {$cabang}\n"
            ."Status : {$statusLabel}\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $order->user,
            phone: $phone,
            type: 'order_status_changed',
            message: $message,
            notifiable: $order,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payment
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Notifikasi status pembayaran gateway.
     */
    public function notifikasiStatusPembayaran(Payment $payment): ?NotificationLog
    {
        $payment->loadMissing(['transaction.booking.user', 'transaction.order.user', 'transaction.branch']);

        $transaction = $payment->transaction;
        $user = $transaction->booking?->user ?? $transaction->order?->user;
        $phone = $user?->phone;

        if (! $phone) {
            return null;
        }

        $judul = match ($payment->status->value) {
            'paid', 'completed' => '*PEMBAYARAN BERHASIL*',
            'failed' => '*PEMBAYARAN GAGAL*',
            'expired' => '*PEMBAYARAN KEDALUWARSA*',
            default => '*STATUS PEMBAYARAN*',
        };

        $statusDesc = match ($payment->status->value) {
            'paid', 'completed' => 'Pembayaran Anda telah berhasil kami terima.',
            'failed' => 'Pembayaran Anda tidak dapat diproses atau mengalami kegagalan.',
            'expired' => 'Batas waktu pembayaran Anda telah berakhir (kedaluwarsa).',
            default => "Status pembayaran Anda saat ini adalah {$payment->status->label()}.",
        };

        $method = strtoupper(str_replace('_', ' ', $payment->payment_method));
        $cabang = $transaction->branch?->name ?? 'SIMAUL';

        $message = "{$judul}\n\n"
            ."Halo {$user->name},\n\n"
            ."{$statusDesc}\n\n"
            ."No. Transaksi : {$transaction->transaction_code}\n"
            ."Metode        : {$method}\n"
            .'Nominal       : Rp '.number_format($payment->amount, 0, ',', '.')."\n"
            ."Status        : {$payment->status->label()}\n"
            ."Cabang        : {$cabang}\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

        return $this->kirim(
            user: $user,
            phone: $phone,
            type: 'payment_status',
            message: $message,
            notifiable: $payment,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Core dispatcher
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kirim pesan dan catat log dengan pencegahan duplikasi dan opsi queue.
     */
    private function kirim(
        ?User $user,
        string $phone,
        string $type,
        string $message,
        ?object $notifiable = null,
    ): NotificationLog {
        $normalizedPhone = WhatsAppService::normalizeIndonesianPhone($phone) ?: $phone;

        // ── Deduplikasi pesan (Idempotency) ──────────────────────────────────
        // Mencegah pengiriman berulang untuk entitas yang sama dalam jendela waktu 2 menit
        $dedupQuery = NotificationLog::where('channel', 'whatsapp')
            ->where('type', $type)
            ->where(function ($q) use ($phone, $normalizedPhone) {
                $q->where('recipient', $phone)
                    ->orWhere('recipient', $normalizedPhone);
            })
            ->where('created_at', '>=', now()->subMinutes(2))
            ->whereIn('status', ['sent', 'pending']);

        if ($notifiable && isset($notifiable->id)) {
            $dedupQuery->where('notifiable_type', get_class($notifiable))
                ->where('notifiable_id', $notifiable->id)
                ->where('message', $message);
        } else {
            $dedupQuery->where('message', $message);
        }

        $existing = $dedupQuery->latest('id')->first();
        if ($existing) {
            Log::info("[Notifikasi] Mencegah duplikasi pesan WhatsApp [{$type}] ke [".WhatsAppService::maskPhone($normalizedPhone).']');

            return $existing;
        }

        $log = new NotificationLog([
            'user_id' => $user?->id,
            'channel' => 'whatsapp',
            'type' => $type,
            'recipient' => $phone,
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        if ($notifiable && isset($notifiable->id)) {
            $log->notifiable_type = get_class($notifiable);
            $log->notifiable_id = $notifiable->id;
        }

        $log->save();

        // ── Mode Queue vs Sinkron ───────────────────────────────────────────
        if (config('services.whatsapp.queue', false)) {
            KirimNotifikasiWhatsAppJob::dispatch(
                $user?->id,
                $phone,
                $type,
                $message,
                $notifiable ? get_class($notifiable) : null,
                $notifiable?->id,
                $log->id,
            );

            return $log;
        }

        try {
            $result = $this->whatsApp->send($phone, $message);

            $log->status = $result['success'] ? 'sent' : 'failed';
            $log->error = $result['success'] ? null : (string) ($result['raw'] ?? 'Unknown error');
            $log->provider_reference = $result['reference'] ?? null;
            $log->sent_at = $result['success'] ? now() : null;
        } catch (\Throwable $e) {
            $log->status = 'failed';
            $log->error = $e->getMessage();

            Log::error('[Notifikasi] WhatsApp send failed', [
                'type' => $type,
                'phone' => WhatsAppService::maskPhone($phone),
                'error' => $e->getMessage(),
            ]);
        }

        $log->save();

        return $log;
    }

    /**
     * Format tanggal dalam bahasa Indonesia (contoh: 15 November 2026).
     */
    private function formatTanggalIndo(mixed $date): string
    {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
    }

    /**
     * Format jam (contoh: 10:00).
     */
    private function formatWaktuIndo(mixed $time): string
    {
        if (! $time) {
            return '-';
        }

        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        $parts = explode(':', (string) $time);
        if (count($parts) >= 2) {
            return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }

        return (string) $time;
    }

    /**
     * Ambil nama layanan booking (mendukung multi-layanan).
     */
    private function resolveBookingServices(Booking $booking): string
    {
        $booking->loadMissing(['service', 'details.service']);

        $services = $booking->details
            ->map(fn ($detail) => $detail->service?->name)
            ->filter()
            ->unique()
            ->values();

        if ($services->isNotEmpty()) {
            return $services->implode(', ');
        }

        return $booking->service?->name ?? 'Layanan SIMAUL';
    }

    /**
     * Ambil nama layanan pesanan drop-off (mendukung multi-layanan).
     */
    private function resolveOrderServices(Order $order): string
    {
        $order->loadMissing(['service', 'details.service']);

        $services = $order->details
            ->map(fn ($detail) => $detail->service?->name)
            ->filter()
            ->unique()
            ->values();

        if ($services->isNotEmpty()) {
            return $services->implode(', ');
        }

        return $order->service?->name ?? 'Layanan SIMAUL';
    }
}
