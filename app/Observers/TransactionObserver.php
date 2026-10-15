<?php

namespace App\Observers;

use App\Enums\StatusTransaksi;
use App\Models\Transaction;
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
            $this->handlePaid($transaction);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged('status') && $transaction->status === StatusTransaksi::Paid) {
            try {
                app(KomisiKaryawanService::class)->processTransactionCommission($transaction);
            } catch (\Throwable $e) {
                Log::error("[TransactionObserver] Gagal memproses komisi otomatis transaksi {$transaction->id}: {$e->getMessage()}", [
                    'transaction_id' => $transaction->id,
                    'exception' => $e,
                ]);
            }

            $this->handlePaid($transaction);
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
