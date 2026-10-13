<?php

namespace App\Modules\KasirPOS\Services;

use App\Enums\StatusPembayaran;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sandbox payment gateway adapter — no real API calls.
 * Simulates QRIS, e-wallet, and virtual account responses locally.
 *
 * Switch to a real provider (e.g. Midtrans, Xendit) by binding
 * a different implementation of PaymentGatewayInterface in
 */
class SandboxPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Simulated gateway name.
     */
    public function getName(): string
    {
        return 'sandbox';
    }

    /**
     * Simulasi pembayaran gateway.
     *
     * @param  array{
     *     method: string,
     *     channel: string|null,
     *     amount: float,
     *     description: string|null,
     *     customer_name: string|null,
     *     customer_email: string|null,
     * }  $options
     */
    public function initiate(Transaction $transaction, array $options): Payment
    {
        $method = $options['method'];
        $channel = $options['channel'] ?? $method;
        $amount = (float) $options['amount'];
        $gatewayRef = 'SBX-'.strtoupper(Str::random(12));

        // Simulated TTL per method (minutes)
        $ttl = match ($method) {
            'qris' => 15,
            'virtual_account' => 60,
            'e_wallet' => 30,
            default => 30,
        };

        $expiredAt = Carbon::now()->addMinutes($ttl);

        // Simulasi response gateway
        $payload = $this->buildSimulatedPayload($method, $channel, $gatewayRef, $amount, $expiredAt, $options);

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => $method,
            'channel' => $channel,
            'amount' => $amount,
            'status' => StatusPembayaran::Pending,
            'reference_number' => null,
            'gateway_reference' => $gatewayRef,
            'gateway_payload' => $payload,
            'paid_at' => null,
            'expired_at' => $expiredAt,
        ]);

        Log::info('[Sandbox] Payment initiated', [
            'payment_id' => $payment->id,
            'transaction_id' => $transaction->id,
            'method' => $method,
            'gateway_ref' => $gatewayRef,
        ]);

        return $payment;
    }

    /**
     * Simulasi webhook callback.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): Payment
    {
        $gatewayRef = $payload['order_id'] ?? $payload['gateway_reference'] ?? null;

        if (! $gatewayRef) {
            throw new RuntimeException('[Sandbox] Webhook missing order_id.');
        }

        $payment = Payment::where('gateway_reference', $gatewayRef)->firstOrFail();

        // Skip if already finalized
        if ($payment->status->isFinal()) {
            return $payment;
        }

        $newStatus = $this->resolveWebhookStatus($payload['transaction_status'] ?? 'settlement');

        $this->applyStatus($payment, $newStatus, $payload);

        Log::info('[Sandbox] Webhook processed', [
            'payment_id' => $payment->id,
            'status' => $newStatus->value,
        ]);

        return $payment->fresh();
    }

    /**
     * Cek status pembayaran gateway.
     * Sandbox simulates success for non-expired pending payments.
     */
    public function checkStatus(Payment $payment): Payment
    {
        if ($payment->status->isFinal()) {
            return $payment;
        }

        // Simulate: expired if past expiry
        if ($payment->expired_at && $payment->expired_at->isPast()) {
            $this->applyStatus($payment, StatusPembayaran::Expired, []);

            return $payment->fresh();
        }

        // In sandbox, keep pending unless webhook fires (no auto-simulation here)
        return $payment;
    }

    /**
     * Simulasi pembayaran sukses.
     */
    public function simulateSuccess(Payment $payment): Payment
    {
        $this->applyStatus($payment, StatusPembayaran::Paid, ['simulated' => true]);

        return $payment->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a simulated gateway response payload.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function buildSimulatedPayload(
        string $method,
        string $channel,
        string $gatewayRef,
        float $amount,
        Carbon $expiredAt,
        array $options,
    ): array {
        $base = [
            'gateway' => 'sandbox',
            'order_id' => $gatewayRef,
            'amount' => $amount,
            'expired_at' => $expiredAt->toIso8601String(),
            'customer_name' => $options['customer_name'] ?? null,
            'customer_email' => $options['customer_email'] ?? null,
        ];

        return match ($method) {
            'qris' => array_merge($base, [
                'qr_string' => '00020101021226570014ID.CO.SANDBOX.WWW011893600914'.rand(1000000000, 9999999999),
                'qr_url' => 'https://sandbox.payment.example.com/qr/'.$gatewayRef,
            ]),
            'virtual_account' => array_merge($base, [
                'bank' => strtoupper($channel),
                'va_number' => '8'.str_pad((string) rand(1, 999999999), 9, '0', STR_PAD_LEFT),
                'va_display' => strtoupper($channel).' Virtual Account',
            ]),
            'e_wallet' => array_merge($base, [
                'checkout_url' => 'https://sandbox.payment.example.com/ewallet/'.$gatewayRef,
                'deeplink_url' => 'https://sandbox.payment.example.com/deeplink/'.$gatewayRef,
                'wallet_type' => strtoupper($channel),
            ]),
            default => $base,
        };
    }

    /**
     * Mapping status pembayaran gateway.
     */
    private function resolveWebhookStatus(string $gatewayStatus): StatusPembayaran
    {
        return match ($gatewayStatus) {
            'settlement', 'capture', 'paid', 'success' => StatusPembayaran::Paid,
            'deny', 'cancel', 'failure' => StatusPembayaran::Failed,
            'expire' => StatusPembayaran::Expired,
            default => StatusPembayaran::Pending,
        };
    }

    /**
     * Simpan status pembayaran.
     *
     * @param  array<string, mixed>  $rawPayload
     */
    private function applyStatus(Payment $payment, StatusPembayaran $status, array $rawPayload): void
    {
        $payment->status = $status;
        $payment->gateway_payload = array_merge($payment->gateway_payload ?? [], ['webhook' => $rawPayload]);

        if ($status === StatusPembayaran::Paid) {
            $payment->paid_at = Carbon::now();
            $payment->reference_number = $payment->gateway_reference;
        }

        $payment->save();
    }
}
