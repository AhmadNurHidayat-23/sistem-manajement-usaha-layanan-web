<?php

namespace App\Services\KasirPOS;

use App\Models\Payment;
use App\Models\Transaction;

/**
 * Contract that every payment gateway adapter must implement.
 * Swap providers by binding a different implementation in a service provider.
 */
interface PaymentGatewayInterface
{
    /**
     * Mulai pembayaran via gateway.
     *
     * gateway-specific data (QR code URL, VA number, redirect URL) stored
     * in gateway_payload.
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
    public function initiate(Transaction $transaction, array $options): Payment;

    /**
     * Webhook callback payment gateway.
     *
     * @param  array<string, mixed>  $payload  Raw decoded webhook body
     */
    public function handleWebhook(array $payload): Payment;

    /**
     * Cek status pembayaran.
     */
    public function checkStatus(Payment $payment): Payment;

    /**
     * Nama slug gateway pembayaran.
     */
    public function getName(): string;
}
