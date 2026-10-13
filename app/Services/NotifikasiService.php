<?php

namespace App\Services;

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
    public function kirimStrukTransaksi(Transaction $transaction): ?NotificationLog
    {
        $transaction->loadMissing(['branch', 'details.service', 'payments', 'booking.user', 'order.user']);

        $user = $transaction->booking?->user ?? $transaction->order?->user;
        $phone = $user?->phone;

        if (! $phone) {
            return null;
        }

        $items = $transaction->details->map(function ($d) {
            $qty = (int) $d->qty === $d->qty ? (int) $d->qty : $d->qty;

            return "• {$d->service->name} (x{$qty}) : Rp ".number_format($d->subtotal, 0, ',', '.');
        })->implode("\n");

        $payment = $transaction->payments->first();
        $paymentInfo = $payment
            ? strtoupper(str_replace('_', ' ', $payment->payment_method)).' ('.$payment->status->label().')'
            : '—';

        $tanggal = Carbon::parse($transaction->transaction_date)->format('d M Y H:i');
        $diskonText = $transaction->discount > 0
            ? 'Diskon        : -Rp '.number_format($transaction->discount, 0, ',', '.')."\n"
            : '';

        $message = "*STRUK TRANSAKSI*\n\n"
            ."Halo {$user->name},\n\n"
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
        $booking->loadMissing(['user', 'service', 'branch']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $tanggal = Carbon::parse($booking->booking_date)->format('d M Y');
        $waktu = $booking->booking_time;

        $message = "*BOOKING DIBUAT*\n\n"
            ."Halo {$booking->user->name},\n\n"
            ."Booking layanan Anda telah kami terima dan saat ini menunggu konfirmasi.\n\n"
            ."Kode Booking : {$booking->booking_code}\n"
            ."Tanggal      : {$tanggal}\n"
            ."Waktu        : {$waktu}\n"
            ."Cabang       : {$booking->branch->name}\n"
            ."Layanan      : {$booking->service->name}\n"
            ."Status       : Menunggu Konfirmasi\n\n"
            ."Kami akan menginformasikan kembali setelah booking dikonfirmasi.\n\n"
            .'Terima kasih telah menggunakan SIMAUL.';

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
        $booking->loadMissing(['user', 'service', 'branch', 'employee']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $tanggal = Carbon::parse($booking->booking_date)->format('d M Y');
        $waktu = $booking->booking_time;
        $petugas = $booking->employee ? "Petugas      : {$booking->employee->name}\n" : '';

        $message = "*BOOKING DIKONFIRMASI*\n\n"
            ."Halo {$booking->user->name},\n\n"
            ."Booking Anda telah dikonfirmasi.\n\n"
            ."Kode Booking : {$booking->booking_code}\n"
            ."Tanggal      : {$tanggal}\n"
            ."Waktu        : {$waktu}\n"
            ."Cabang       : {$booking->branch->name}\n"
            ."Layanan      : {$booking->service->name}\n"
            ."{$petugas}\n"
            ."Silakan hadir sesuai jadwal yang telah ditentukan.\n\n"
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
        $booking->loadMissing(['user', 'service', 'branch']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $judul = match ($booking->status->value) {
            'confirmed' => '*BOOKING DIKONFIRMASI*',
            'in_progress' => '*BOOKING SEDANG DIKERJAKAN*',
            'completed' => '*BOOKING SELESAI*',
            'cancelled' => '*BOOKING DIBATALKAN*',
            default => '*UPDATE STATUS BOOKING*',
        };

        $statusLabel = match ($booking->status->value) {
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($booking->status->value),
        };

        $statusDesc = match ($booking->status->value) {
            'confirmed' => 'Booking Anda telah dikonfirmasi.',
            'in_progress' => 'Layanan booking Anda saat ini sedang dikerjakan oleh tim kami.',
            'completed' => 'Layanan booking Anda telah selesai dikerjakan.',
            'cancelled' => 'Booking Anda telah dibatalkan.',
            default => 'Status booking Anda telah diperbarui.',
        };

        $tanggal = Carbon::parse($booking->booking_date)->format('d M Y');
        $waktu = $booking->booking_time;

        $message = "{$judul}\n\n"
            ."Halo {$booking->user->name},\n\n"
            ."{$statusDesc}\n\n"
            ."Kode Booking : {$booking->booking_code}\n"
            ."Tanggal      : {$tanggal}\n"
            ."Waktu        : {$waktu}\n"
            ."Cabang       : {$booking->branch->name}\n"
            ."Layanan      : {$booking->service->name}\n"
            ."Status       : {$statusLabel}\n\n"
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
        $booking->loadMissing(['user', 'service', 'branch']);

        $phone = $booking->user?->phone;

        if (! $phone) {
            return null;
        }

        $tanggal = Carbon::parse($booking->booking_date)->format('d M Y');
        $waktu = $booking->booking_time;

        $message = "*PENGINGAT JADWAL BOOKING*\n\n"
            ."Halo {$booking->user->name},\n\n"
            ."Ini adalah pengingat untuk jadwal booking layanan Anda.\n\n"
            ."Kode Booking : {$booking->booking_code}\n"
            ."Tanggal      : {$tanggal}\n"
            ."Waktu        : {$waktu}\n"
            ."Cabang       : {$booking->branch->name}\n"
            ."Layanan      : {$booking->service->name}\n\n"
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
        $order->loadMissing(['user', 'service', 'branch']);

        $phone = $order->user?->phone;

        if (! $phone) {
            return null;
        }

        $finishText = $order->estimated_finish_at
            ? Carbon::parse($order->estimated_finish_at)->format('d M Y H:i')
            : 'Menyesuaikan antrean';

        $message = "*PESANAN DIBUAT*\n\n"
            ."Halo {$order->user->name},\n\n"
            ."Pesanan layanan Anda telah kami terima dan masuk dalam antrean pengerjaan.\n\n"
            ."No. Pesanan      : #{$order->id}\n"
            ."Layanan          : {$order->service->name}\n"
            ."Cabang           : {$order->branch->name}\n"
            ."Estimasi Selesai : {$finishText}\n"
            ."Status           : Diterima\n\n"
            ."Kami akan menginformasikan kembali jika pesanan telah siap diambil.\n\n"
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
     * Notifikasi perubahan status pesanan.
     */
    public function notifikasiStatusOrder(Order $order): ?NotificationLog
    {
        $order->loadMissing(['user', 'service', 'branch']);

        $phone = $order->user?->phone;

        if (! $phone) {
            return null;
        }

        $judul = match ($order->status->value) {
            'received' => '*PESANAN DITERIMA*',
            'processing' => '*PESANAN SEDANG DIKERJAKAN*',
            'ready' => '*PESANAN SIAP DIAMBIL*',
            'completed' => '*PESANAN SELESAI*',
            'cancelled' => '*PESANAN DIBATALKAN*',
            default => '*UPDATE STATUS PESANAN*',
        };

        $statusLabel = match ($order->status->value) {
            'pending' => 'Menunggu Konfirmasi',
            'received' => 'Diterima',
            'processing' => 'Sedang Dikerjakan',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($order->status->value),
        };

        $statusDesc = match ($order->status->value) {
            'ready' => 'Pesanan Anda telah selesai dikerjakan dan siap untuk diambil.',
            'processing' => 'Pesanan Anda saat ini sedang dalam proses pengerjaan oleh tim kami.',
            'completed' => 'Pesanan Anda telah selesai dan telah diserahkan.',
            'cancelled' => 'Pesanan Anda telah dibatalkan.',
            'received' => 'Pesanan Anda telah diterima oleh cabang kami.',
            default => 'Status pesanan Anda telah diperbarui.',
        };

        $message = "{$judul}\n\n"
            ."Halo {$order->user->name},\n\n"
            ."{$statusDesc}\n\n"
            ."No. Pesanan : #{$order->id}\n"
            ."Layanan     : {$order->service->name}\n"
            ."Cabang      : {$order->branch->name}\n"
            ."Status      : {$statusLabel}\n\n"
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
     * Kirim pesan dan catat log.
     */
    private function kirim(
        ?User $user,
        string $phone,
        string $type,
        string $message,
        ?object $notifiable = null,
    ): NotificationLog {
        $log = new NotificationLog([
            'user_id' => $user?->id,
            'channel' => 'whatsapp',
            'type' => $type,
            'recipient' => $phone,
            'message' => $message,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        if ($notifiable) {
            $log->notifiable_type = get_class($notifiable);
            $log->notifiable_id = $notifiable->id;
        }

        $log->save();

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
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        $log->save();

        return $log;
    }
}
