<?php

namespace App\Services\KasirPOS;

use App\Enums\StatusPembayaran;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PembayaranService
{
    /**
     * Proses pembayaran transaksi.
     *
     * @param  array{
     *     payment_method: string,
     *     amount: float,
     *     cash_received?: ?float,
     *     reference_number?: ?string
     * }  $paymentData
     */
    public function processPayment(Transaction $transaction, array $paymentData): Payment
    {
        $method = $paymentData['payment_method'];
        $amount = (float) $paymentData['amount'];
        $cashReceived = isset($paymentData['cash_received']) ? (float) $paymentData['cash_received'] : $amount;
        $ref = $paymentData['reference_number'] ?? null;

        if ($method === 'cash') {
            $change = max(0, $cashReceived - $amount);
            $refText = 'Tunai | Bayar: Rp '.number_format($cashReceived, 0, ',', '.').' | Kembali: Rp '.number_format($change, 0, ',', '.');
            if ($ref) {
                $refText .= ' ('.$ref.')';
            }
            $referenceNumber = $refText;
        } else {
            $referenceNumber = $ref ?: strtoupper($method).'-'.strtoupper(Str::random(8));
        }

        return Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => $method,
            'amount' => $amount,
            'status' => StatusPembayaran::Completed,
            'reference_number' => $referenceNumber,
            'paid_at' => isset($paymentData['paid_at']) ? Carbon::parse($paymentData['paid_at']) : Carbon::now(),
        ]);
    }
}
