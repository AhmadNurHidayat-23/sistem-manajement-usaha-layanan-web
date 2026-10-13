<?php

namespace App\Modules\KasirPOS\Services;

use App\Enums\StatusPembayaran;
use App\Models\Transaction;
use RuntimeException;

/**
 * Helper untuk menyusun payload request dan mem-parsing response/status Midtrans API.
 */
class MidtransPayloadBuilder
{
    /**
     * Susun payload charge QRIS Core API Midtrans.
     *
     * @param  array{customer_name?: ?string, customer_email?: ?string}  $options
     * @return array<string, mixed>
     */
    public function buildCoreQrisPayload(string $orderId, float $amount, array $options): array
    {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        if (! empty($options['customer_name']) || ! empty($options['customer_email'])) {
            $payload['customer_details'] = array_filter([
                'first_name' => $options['customer_name'] ?? 'Pelanggan',
                'email' => $options['customer_email'] ?? null,
            ]);
        }

        return $payload;
    }

    /**
     * Parse action URLs dari respons QRIS Midtrans.
     * Mengutamakan generate-qr-code-v2 (ASPI standard untuk sandbox simulator baru),
     * dengan fallback ke generate-qr-code (v1).
     *
     * @param  array<string, mixed>  $responseData
     * @return array{qr_url: ?string, qr_url_v1: ?string, qr_url_v2: ?string, qr_string: ?string}
     */
    public function parseQrisActions(array $responseData): array
    {
        $qrUrlV1 = null;
        $qrUrlV2 = null;

        if (! empty($responseData['actions']) && is_array($responseData['actions'])) {
            foreach ($responseData['actions'] as $action) {
                $name = $action['name'] ?? '';
                if ($name === 'generate-qr-code-v2') {
                    $qrUrlV2 = $action['url'] ?? null;
                } elseif ($name === 'generate-qr-code') {
                    $qrUrlV1 = $action['url'] ?? null;
                }
            }
        }

        return [
            'qr_url' => $qrUrlV2 ?: $qrUrlV1,
            'qr_url_v1' => $qrUrlV1,
            'qr_url_v2' => $qrUrlV2,
            'qr_string' => $responseData['qr_string'] ?? null,
        ];
    }

    /**
     * Susun payload transaksi Snap API Midtrans.
     *
     * @param  array{customer_name?: ?string, customer_email?: ?string}  $options
     * @return array<string, mixed>
     */
    public function buildSnapPayload(
        Transaction $transaction,
        string $orderId,
        float $amount,
        string $method,
        array $options
    ): array {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $options['customer_name'] ?: 'Pelanggan',
                'email' => $options['customer_email'] ?: null,
            ],
        ];

        if ($transaction->details->isNotEmpty()) {
            $itemDetails = [];
            foreach ($transaction->details as $detail) {
                $itemDetails[] = [
                    'id' => (string) $detail->service_id,
                    'price' => (int) round((float) $detail->price),
                    'quantity' => (int) max(1, round((float) $detail->qty)),
                    'name' => mb_substr($detail->service?->name ?? 'Layanan', 0, 50),
                ];
            }

            if ($transaction->discount > 0) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT',
                    'price' => -(int) round((float) $transaction->discount),
                    'quantity' => 1,
                    'name' => 'Potongan Diskon Promo',
                ];
            }

            $itemsSum = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $itemDetails));
            if ($itemsSum === (int) round($amount)) {
                $params['item_details'] = $itemDetails;
            }
        }

        $enabledPayments = match ($method) {
            'qris' => ['other_qris', 'gopay', 'shopeepay'],
            'virtual_account' => ['bca_va', 'bni_va', 'bri_va', 'permata_va', 'other_va', 'echannel'],
            'e_wallet' => ['gopay', 'shopeepay'],
            default => null,
        };

        if ($enabledPayments) {
            $params['enabled_payments'] = $enabledPayments;
        }

        $params['callbacks'] = [
            'finish' => route('pos.transactions.receipt', $transaction),
        ];

        return $params;
    }

    /**
     * Validasi signature key webhook: SHA512(order_id + status_code + gross_amount + server_key).
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifySignatureKey(array $payload, ?string $serverKey): void
    {
        if (empty($serverKey) || empty($payload['signature_key'])) {
            return;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) $payload['signature_key'];

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (! hash_equals($expectedSignature, $signatureKey)) {
            throw new RuntimeException('[Midtrans] Invalid webhook signature key.');
        }
    }

    /**
     * Map status transaksi Midtrans ke enum StatusPembayaran sistem.
     */
    public function resolveWebhookStatus(string $transactionStatus, ?string $fraudStatus): StatusPembayaran
    {
        return match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? StatusPembayaran::Pending : StatusPembayaran::Paid,
            'settlement', 'success' => StatusPembayaran::Paid,
            'pending' => StatusPembayaran::Pending,
            'deny', 'cancel' => StatusPembayaran::Failed,
            'expire' => StatusPembayaran::Expired,
            'refund', 'partial_refund' => StatusPembayaran::Refunded,
            default => StatusPembayaran::Pending,
        };
    }
}
