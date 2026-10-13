<?php

namespace App\Modules\KasirPOS\Services;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Payment;
use App\Models\Transaction;
use App\Modules\Karyawan\Services\KomisiKaryawanService;
use App\Modules\MembershipPromo\Services\MemberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Orchestrates payment gateway operations.
 * Implementasi gateway pembayaran.
 *
 * Supported methods: 'qris', 'e_wallet', 'virtual_account'
 *
 * Integrasi payment gateway:
 * 1. Create a new class implementing PaymentGatewayInterface
 * 2. In AppServiceProvider::register(), bind:
 *    $this->app->bind(PaymentGatewayInterface::class, YourGateway::class)
 * 3. Add provider credentials to .env (see .env.example)
 */
class PaymentGatewayService
{
    /** @var string[] */
    private const SUPPORTED_METHODS = ['qris', 'e_wallet', 'virtual_account'];

    public function __construct(protected PaymentGatewayInterface $gateway) {}

    /**
     * Mulai pembayaran gateway.
     *
     * @param  array{
     *     method: string,
     *     channel: string|null,
     *     description: string|null,
     *     customer_name: string|null,
     *     customer_email: string|null,
     * }  $options
     */
    public function initiate(Transaction $transaction, array $options): Payment
    {
        $method = $options['method'] ?? '';

        if (! in_array($method, self::SUPPORTED_METHODS)) {
            throw new InvalidArgumentException("Payment method '{$method}' is not supported. Use: ".implode(', ', self::SUPPORTED_METHODS));
        }

        // Cek transaksi pending
        $existing = $transaction->payments()
            ->where('status', StatusPembayaran::Pending->value)
            ->whereIn('payment_method', self::SUPPORTED_METHODS)
            ->whereNull('paid_at')
            ->first();

        if ($existing && $existing->expired_at?->isFuture()) {
            if ($existing->payment_method === $method) {
                return $existing;
            }
            $existing->update(['status' => StatusPembayaran::Expired]);
        } elseif ($existing && $existing->expired_at?->isPast()) {
            $existing->update(['status' => StatusPembayaran::Expired]);
        }

        $options['amount'] = (float) $transaction->total;

        return DB::transaction(function () use ($transaction, $options) {
            return $this->gateway->initiate($transaction, $options);
        });
    }

    /**
     * Proses webhook callback.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): Payment
    {
        $payment = $this->gateway->handleWebhook($payload);

        // Update status transaksi
        if ($payment->isPaid()) {
            $this->syncTransactionStatus($payment);
        }

        return $payment;
    }

    /**
     * Cek status pembayaran gateway.
     */
    public function checkStatus(Payment $payment): Payment
    {
        $payment = $this->gateway->checkStatus($payment);

        if ($payment->isPaid()) {
            $this->syncTransactionStatus($payment);
        }

        return $payment;
    }

    /**
     * Simulate a successful payment (sandbox/demo only).
     * Throws in production mode.
     */
    public function simulateSuccess(Payment $payment): Payment
    {
        if ($this->gateway instanceof SandboxPaymentGateway) {
            $payment = $this->gateway->simulateSuccess($payment);
        } elseif ($this->gateway instanceof MidtransPaymentGateway && ! config('services.payment_gateway.midtrans.is_production', false)) {
            $payment = $this->gateway->simulateSuccess($payment);
        } else {
            throw new \RuntimeException('simulateSuccess is only available for the sandbox gateway.');
        }

        $this->syncTransactionStatus($payment);

        return $payment;
    }

    /**
     * Tandai transaksi lunas.
     */
    private function syncTransactionStatus(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $transaction = Transaction::where('id', $payment->transaction_id)
                ->lockForUpdate()
                ->first();

            if (! $transaction || $transaction->status === StatusTransaksi::Paid) {
                return;
            }

            $transaction->status = StatusTransaksi::Paid;
            $transaction->save();

            // Tambah poin loyalitas setelah transaksi lunas via gateway
            rescue(fn () => app(MemberService::class)->awardPointsFromTransaction($transaction));

            // Hitung estimasi komisi otomatis jika transaksi dan pengerjaan telah selesai (KRY-F-004)
            app(KomisiKaryawanService::class)->processTransactionCommission($transaction);

            Log::info('[PaymentGateway] Transaction marked as paid', [
                'transaction_id' => $transaction->id,
                'payment_id' => $payment->id,
            ]);
        });
    }

    /**
     * Nama gateway aktif.
     */
    public function getGatewayName(): string
    {
        return $this->gateway->getName();
    }

    /**
     * Daftar metode pembayaran.
     *
     * @return string[]
     */
    public function getSupportedMethods(): array
    {
        return self::SUPPORTED_METHODS;
    }
}
