<?php

namespace App\Services\Notifications;

use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Notifications\OperationalNotification;
use Illuminate\Notifications\DatabaseNotification;

class CashierNotificationService
{
    /**
     * Beri tahu kasir cabang tentang pembayaran yang menunggu penanganan di POS.
     */
    public function notifyPaymentPending(Branch $branch, Booking|Order|Transaction $source, float $amount, string $serviceName): void
    {
        $type = $source instanceof Booking ? 'booking' : ($source instanceof Order ? 'order' : 'transaction');
        $cashiers = NotificationHelper::getBranchStaff($branch->id, 'cashier');

        $actionUrl = match ($type) {
            'booking' => route('pos.index', ['booking_id' => $source->uuid ?? $source->id]),
            'order' => route('pos.index', ['order_id' => $source->uuid ?? $source->id]),
            default => route('pos.index'),
        };

        foreach ($cashiers as $cashier) {
            $key = "cashier_pending_{$type}_{$source->id}_{$cashier->id}";
            if (NotificationHelper::hasKey($cashier, $key)) {
                continue;
            }

            $cashier->notify(new OperationalNotification(
                title: 'Pembayaran Menunggu',
                message: "{$serviceName} memiliki tagihan Rp ".number_format($amount, 0, ',', '.').' yang perlu diproses.',
                category: 'payment_pending',
                statusLevel: 'warning',
                actionUrl: $actionUrl,
                actionLabel: 'Bayar di POS',
                details: ['type' => $type, 'id' => $source->id, 'branch_id' => $branch->id],
                idempotencyKey: $key,
            ));
        }
    }

    /**
     * Beri tahu kasir cabang saat pembayaran Lunas (QRIS, E-Wallet, VA, Tunai).
     */
    public function notifyPaymentSuccess(Transaction $transaction, ?Payment $payment = null): void
    {
        if ($transaction->status !== StatusTransaksi::Paid) {
            return;
        }

        $cashiers = NotificationHelper::getBranchStaff($transaction->branch_id, 'cashier');
        $method = $payment?->payment_method ?? $transaction->payments()->latest()->value('payment_method') ?? 'tunai';
        $methodLabel = match (strtolower((string) $method)) {
            'qris' => 'QRIS',
            'e_wallet' => 'E-Wallet',
            'virtual_account' => 'Virtual Account',
            'cash' => 'Tunai',
            default => strtoupper((string) $method),
        };

        $transaction->loadMissing(['branch', 'details.service', 'booking.user', 'order.user']);
        $serviceName = $transaction->details->first()?->service?->name ?? 'Layanan';
        $receiptUrl = route('pos.transactions.receipt', $transaction);

        foreach ($cashiers as $cashier) {
            $key = "cashier_paid_{$transaction->id}_{$cashier->id}";
            if (NotificationHelper::hasKey($cashier, $key)) {
                continue;
            }

            $cashier->notify(new OperationalNotification(
                title: 'Pembayaran Berhasil',
                message: "Pembayaran {$methodLabel} untuk layanan {$serviceName} telah berhasil.",
                category: 'payment_success',
                statusLevel: 'success',
                actionUrl: $receiptUrl,
                actionLabel: 'Lihat / Cetak Struk',
                details: [
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code,
                    'total' => $transaction->total,
                    'method' => $methodLabel,
                ],
                idempotencyKey: $key,
            ));
        }

        $this->resolvePendingNotifications($transaction, $receiptUrl);
    }

    /**
     * Tandai notifikasi pending yang berkaitan dengan transaksi ini menjadi selesai/mengarah ke struk.
     */
    protected function resolvePendingNotifications(Transaction $transaction, string $receiptUrl): void
    {
        $keys = [];
        if ($transaction->booking_id) {
            $keys[] = "cashier_pending_booking_{$transaction->booking_id}";
        }
        if ($transaction->order_id) {
            $keys[] = "cashier_pending_order_{$transaction->order_id}";
        }
        $keys[] = "cashier_pending_transaction_{$transaction->id}";

        foreach ($keys as $k) {
            $notifications = DatabaseNotification::where('data', 'like', "%{$k}%")->get();
            foreach ($notifications as $n) {
                $data = $n->data;
                $data['status_level'] = 'success';
                $data['title'] = 'Pembayaran Berhasil';
                $data['action_url'] = $receiptUrl;
                $data['action_label'] = 'Lihat / Cetak Struk';
                $data['is_resolved'] = true;
                $n->data = $data;
                $n->save();
            }
        }
    }
}
