<?php

namespace App\Modules\KasirPOS\Services;

use App\Enums\StatusPembayaran;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Midtrans Payment Gateway Adapter (Snap API & Core API Sandbox/Production).
 */
class MidtransPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected ?MidtransPayloadBuilder $payloadBuilder = null,
        protected ?string $serverKey = null,
        protected ?string $clientKey = null,
        protected bool $isProduction = false
    ) {
        $this->serverKey = $serverKey ?? config('services.payment_gateway.midtrans.server_key');
        $this->clientKey = $clientKey ?? config('services.payment_gateway.midtrans.client_key');
        $this->isProduction = $isProduction || (bool) config('services.payment_gateway.midtrans.is_production', false);
        $this->payloadBuilder = $payloadBuilder ?? new MidtransPayloadBuilder;
    }

    public function getName(): string
    {
        return 'midtrans';
    }

    /**
     * @param  array{method: string, channel: ?string, amount: float, description: ?string, customer_name: ?string, customer_email: ?string}  $options
     */
    public function initiate(Transaction $transaction, array $options): Payment
    {
        if (empty($this->serverKey)) {
            throw new RuntimeException('Midtrans server key is not configured. Silakan atur MIDTRANS_SERVER_KEY di .env');
        }

        $method = $options['method'];
        $channel = $options['channel'] ?? $method;
        $amount = (float) $options['amount'];
        $gatewayRef = 'MID-'.$transaction->transaction_code.'-'.strtoupper(Str::random(6));

        $ttl = match ($method) {
            'qris' => 15, 'virtual_account' => 60, default => 30
        };
        $expiredAt = Carbon::now()->addMinutes($ttl);

        if ($method === 'qris') {
            return $this->initiateQrisCoreApi($transaction, $gatewayRef, $amount, $channel, $options, $expiredAt);
        }

        $transaction->loadMissing('details.service');
        $snapPayload = $this->payloadBuilder->buildSnapPayload($transaction, $gatewayRef, $amount, $method, $options);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($this->getSnapEndpoint(), $snapPayload);

        if (! $response->successful()) {
            $errorMsg = $response->json('error_messages.0') ?? $response->body();
            Log::error('[Midtrans] Failed creating Snap transaction', [
                'order_id' => $gatewayRef,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new RuntimeException('Gagal membuat transaksi pembayaran Midtrans: '.$errorMsg);
        }

        $responseData = $response->json();
        $payload = [
            'gateway' => 'midtrans',
            'order_id' => $gatewayRef,
            'amount' => $amount,
            'token' => $responseData['token'] ?? null,
            'redirect_url' => $responseData['redirect_url'] ?? null,
            'checkout_url' => $responseData['redirect_url'] ?? null,
            'expired_at' => $expiredAt->toIso8601String(),
            'customer_name' => $options['customer_name'] ?? null,
            'customer_email' => $options['customer_email'] ?? null,
            'raw_response' => $responseData,
        ];

        return Payment::create([
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
    }

    /**
     * Inisiasi QRIS via Midtrans Core API (POST /v2/charge).
     *
     * @param  array{customer_name: ?string, customer_email: ?string}  $options
     */
    protected function initiateQrisCoreApi(
        Transaction $transaction,
        string $gatewayRef,
        float $amount,
        string $channel,
        array $options,
        Carbon $expiredAt
    ): Payment {
        $endpoint = $this->getApiEndpoint().'/v2/charge';
        $corePayload = $this->payloadBuilder->buildCoreQrisPayload($gatewayRef, $amount, $options);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($endpoint, $corePayload);

        if (! $response->successful()) {
            $errorMsg = $response->json('status_message')
                ?? $response->json('error_messages.0')
                ?? $response->body();

            Log::error('[Midtrans] Failed creating QRIS Core API transaction', [
                'order_id' => $gatewayRef,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new RuntimeException('Gagal membuat transaksi QRIS Midtrans: '.$errorMsg);
        }

        $responseData = $response->json();
        $actions = $this->payloadBuilder->parseQrisActions($responseData);

        if (! empty($responseData['expiry_time'])) {
            try {
                $expiredAt = Carbon::parse($responseData['expiry_time']);
            } catch (\Throwable) {
                // pertahankan default expiredAt
            }
        }

        $payload = [
            'gateway' => 'midtrans',
            'order_id' => $gatewayRef,
            'amount' => $amount,
            'qr_url' => $actions['qr_url'],
            'qr_url_v1' => $actions['qr_url_v1'],
            'qr_url_v2' => $actions['qr_url_v2'],
            'qr_string' => $actions['qr_string'],
            'redirect_url' => null,
            'checkout_url' => null,
            'expired_at' => $expiredAt->toIso8601String(),
            'customer_name' => $options['customer_name'] ?? null,
            'customer_email' => $options['customer_email'] ?? null,
            'raw_response' => $responseData,
        ];

        return Payment::create([
            'transaction_id' => $transaction->id,
            'payment_method' => 'qris',
            'channel' => $channel,
            'amount' => $amount,
            'status' => StatusPembayaran::Pending,
            'reference_number' => $responseData['transaction_id'] ?? null,
            'gateway_reference' => $gatewayRef,
            'gateway_payload' => $payload,
            'paid_at' => null,
            'expired_at' => $expiredAt,
        ]);
    }

    public function simulateSuccess(Payment $payment): Payment
    {
        if ($this->isProduction) {
            throw new RuntimeException('simulateSuccess is not allowed in production.');
        }

        $payment->status = StatusPembayaran::Paid;
        $payment->paid_at = Carbon::now();
        $payment->reference_number = 'SIM-MID-'.Str::random(10);
        $payment->save();

        return $payment->fresh();
    }

    /**
     * Webhook notification handler dari Midtrans dengan proteksi idempoten.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): Payment
    {
        $gatewayRef = $payload['order_id'] ?? $payload['gateway_reference'] ?? null;
        if (! $gatewayRef) {
            throw new RuntimeException('[Midtrans] Webhook missing order_id.');
        }

        return DB::transaction(function () use ($gatewayRef, $payload) {
            $payment = Payment::where('gateway_reference', $gatewayRef)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status->isFinal()) {
                return $payment;
            }

            $this->payloadBuilder->verifySignatureKey($payload, $this->serverKey);

            $newStatus = $this->payloadBuilder->resolveWebhookStatus(
                $payload['transaction_status'] ?? 'pending',
                $payload['fraud_status'] ?? null
            );

            $payment->status = $newStatus;
            $payment->gateway_payload = array_merge($payment->gateway_payload ?? [], [
                'webhook' => $payload,
            ]);

            if ($newStatus === StatusPembayaran::Paid) {
                $payment->paid_at = Carbon::now();
                $payment->reference_number = (string) ($payload['transaction_id'] ?? $payment->gateway_reference);
            }

            $payment->save();

            Log::info('[Midtrans] Webhook processed successfully', [
                'payment_id' => $payment->id,
                'order_id' => $gatewayRef,
                'status' => $newStatus->value,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Cek status pembayaran ke server Midtrans.
     * Mengutamakan verifikasi server-to-server sebelum status kedaluwarsa lokal.
     */
    public function checkStatus(Payment $payment): Payment
    {
        if ($payment->status->isFinal()) {
            return $payment;
        }

        if (! empty($this->serverKey) && ! empty($payment->gateway_reference)) {
            try {
                $url = $this->getApiEndpoint().'/v2/'.$payment->gateway_reference.'/status';

                $response = Http::withBasicAuth($this->serverKey, '')
                    ->acceptJson()
                    ->timeout(10)
                    ->get($url);

                if ($response->successful()) {
                    return $this->handleWebhook($response->json());
                }
            } catch (\Throwable $e) {
                Log::warning('[Midtrans] Check status API call failed: '.$e->getMessage(), [
                    'payment_id' => $payment->id,
                ]);
            }
        }

        if ($payment->expired_at && $payment->expired_at->isPast()) {
            $payment->status = StatusPembayaran::Expired;
            $payment->save();

            return $payment->fresh();
        }

        return $payment;
    }

    public function getSnapEndpoint(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getApiEndpoint(): string
    {
        return $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
