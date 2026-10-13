<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OperationalNotification;

class CustomerNotificationService
{
    /**
     * Beri tahu pelanggan tentang perubahan status booking/order (BOK-F-008).
     */
    public function notifyStatus(User $customer, Booking|Order $source, string $event): void
    {
        $type = $source instanceof Booking ? 'booking' : 'order';
        $key = "customer_{$type}_{$source->id}_{$event}";
        if (NotificationHelper::hasKey($customer, $key)) {
            return;
        }

        $service = $source->service?->name ?? 'Layanan';
        [$title, $msg, $level] = match ($event) {
            'confirmed' => ['Booking Dikonfirmasi', "Booking {$service} Anda telah dikonfirmasi.", 'info'],
            'processing', 'in_progress' => ['Layanan Sedang Dikerjakan', "Layanan {$service} Anda saat ini sedang dikerjakan.", 'info'],
            'ready' => ['Pesanan Siap Diambil', "Pesanan drop-off {$service} Anda telah selesai dan siap diambil.", 'success'],
            'completed' => ['Layanan Selesai', "Layanan {$service} Anda telah selesai.", 'success'],
            'cancelled' => ['Layanan Dibatalkan', "Layanan {$service} Anda telah dibatalkan.", 'error'],
            'payment_paid' => ['Pembayaran Berhasil', "Pembayaran untuk {$service} telah kami terima.", 'success'],
            default => ['Update Status Layanan', "Status layanan {$service} telah diperbarui.", 'info'],
        };

        $url = $source instanceof Booking
            ? route('customer.booking.show', $source)
            : route('customer.pesanan.show', $source);

        $customer->notify(new OperationalNotification(
            title: $title,
            message: $msg,
            category: 'status_update',
            statusLevel: $level,
            actionUrl: $url,
            actionLabel: 'Lihat Detail',
            details: ['type' => $type, 'id' => $source->id, 'event' => $event],
            idempotencyKey: $key,
        ));
    }
}
