<?php

namespace App\Observers;

use App\Enums\StatusTransaksi;
use App\Models\Transaction;
use App\Services\Inventori\InventoriTransaksiService;
use App\Services\Karyawan\KomisiKaryawanService;
use App\Services\OperationalNotificationService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        if ($transaction->status === StatusTransaksi::Pending && $transaction->branch) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyBranchCashiersPaymentPending(
                $transaction->branch,
                $transaction,
                (float) $transaction->total,
                $transaction->transaction_code
            ));
        } elseif ($transaction->status === StatusTransaksi::Paid) {
            try {
                if ($transaction->details()->exists()) {
                    app(InventoriTransaksiService::class)->deductStockForTransaction($transaction);
                }
            } catch (\Throwable $e) {
                Log::error("[TransactionObserver] Gagal memproses pengurangan stok transaksi {$transaction->id}: {$e->getMessage()}", [
                    'transaction_id' => $transaction->id,
                    'exception' => $e,
                ]);
            }

            $this->handlePaid($transaction);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged('status')) {
            if ($transaction->status === StatusTransaksi::Paid) {
                try {
                    app(KomisiKaryawanService::class)->processTransactionCommission($transaction);
                } catch (\Throwable $e) {
                    Log::error("[TransactionObserver] Gagal memproses komisi otomatis transaksi {$transaction->id}: {$e->getMessage()}", [
                        'transaction_id' => $transaction->id,
                        'exception' => $e,
                    ]);
                }

                try {
                    app(InventoriTransaksiService::class)->deductStockForTransaction($transaction);
                } catch (\Throwable $e) {
                    Log::error("[TransactionObserver] Gagal memproses pengurangan stok transaksi {$transaction->id}: {$e->getMessage()}", [
                        'transaction_id' => $transaction->id,
                        'exception' => $e,
                    ]);
                }

                $this->handlePaid($transaction);
            } elseif (in_array($transaction->status, [StatusTransaksi::Cancelled, StatusTransaksi::Refunded], true)) {
                try {
                    app(InventoriTransaksiService::class)->reverseStockForTransaction($transaction);
                } catch (\Throwable $e) {
                    Log::error("[TransactionObserver] Gagal mengembalikan stok transaksi {$transaction->id}: {$e->getMessage()}", [
                        'transaction_id' => $transaction->id,
                        'exception' => $e,
                    ]);
                }
            }
        }
    }

    /**
     * Berikan notifikasi pembayaran lunas ke kasir cabang dan pelanggan.
     */
    protected function handlePaid(Transaction $transaction): void
    {
        rescue(fn () => app(OperationalNotificationService::class)->notifyBranchCashiersPaymentSuccess($transaction));

        $source = $transaction->booking ?? $transaction->order;
        $customer = $transaction->booking?->user ?? $transaction->order?->user;
        if ($customer && $source) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus(
                $customer,
                $source,
                'payment_paid'
            ));
        }
    }
}
