<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic WhatsApp sender.
 *
 * Supports two modes controlled by WHATSAPP_DRIVER in .env:
 *   - log (default/sandbox): writes messages to Laravel log — no real HTTP call
 *   - fonnte: Fonnte.com WhatsApp gateway (popular Indonesian provider)
 *   - wablas: Wablas.com gateway
 *   - custom: any provider with a simple POST endpoint
 *
 * Driver WhatsApp:
 * in .env. No application code changes required.
 *
 * Return value: ['success' => bool, 'reference' => string|null, 'raw' => mixed]
 */
class WhatsAppService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = config('services.whatsapp.driver', 'log');
    }

    /**
     * Kirim pesan WhatsApp.
     * Format nomor dinormalisasi ke 62xxx.
     *
     * @return array{success: bool, reference: string|null, raw: mixed}
     */
    public function send(string $phone, string $message): array
    {
        $phone = $this->normalisePhone($phone);

        if (! $phone) {
            return ['success' => false, 'reference' => null, 'raw' => 'Invalid or missing phone number'];
        }

        return match ($this->driver) {
            'baileys' => $this->sendViaBaileys($phone, $message),
            'fonnte' => $this->sendViaFonnte($phone, $message),
            'wablas' => $this->sendViaWablas($phone, $message),
            'custom' => $this->sendViaCustom($phone, $message),
            default => $this->sendViaLog($phone, $message),
        };
    }

    /**
     * Nama driver aktif.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Providers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Baileys local WhatsApp gateway.
     * Mengirim pesan via HTTP POST ke lokal server Baileys (Express).
     *
     * @return array{success: bool, reference: string|null, raw: mixed}
     */
    private function sendViaBaileys(string $phone, string $message): array
    {
        $baseUrl = rtrim((string) config('services.whatsapp.baileys_url', 'http://127.0.0.1:3001'), '/');

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->acceptJson()
                ->post("{$baseUrl}/send-message", [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            $body = $response->json();
            $success = $response->successful() && ($body['success'] ?? false) === true;
            $reference = $body['messageId'] ?? null;
            $raw = $success
                ? ($body ?? 'success')
                : ($body['error'] ?? (is_array($body) ? json_encode($body) : ($response->body() ?: 'Gagal mengirim pesan via Baileys')));

            return [
                'success' => $success,
                'reference' => $reference,
                'raw' => $raw,
            ];
        } catch (\Throwable $e) {
            Log::warning('[WhatsApp:baileys] Gagal menghubungi gateway Baileys: '.$e->getMessage(), [
                'to' => $phone,
                'url' => "{$baseUrl}/send-message",
            ]);

            return [
                'success' => false,
                'reference' => null,
                'raw' => 'Koneksi ke Baileys gateway gagal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Log mode — no real HTTP call, safe for development/sandbox.
     *
     * @return array{success: bool, reference: string|null, raw: string}
     */
    private function sendViaLog(string $phone, string $message): array
    {
        $ref = 'LOG-'.uniqid();

        Log::channel('stack')->info('[WhatsApp:log] Message sent', [
            'to' => $phone,
            'message' => $message,
            'reference' => $ref,
        ]);

        return ['success' => true, 'reference' => $ref, 'raw' => 'logged'];
    }

    /**
     * Fonnte.com — https://fonnte.com
     * Driver Fonnte.
     *
     * @return array{success: bool, reference: string|null, raw: mixed}
     */
    private function sendViaFonnte(string $phone, string $message): array
    {
        $token = config('services.whatsapp.fonnte_token');

        if (! $token) {
            return [
                'success' => false,
                'reference' => null,
                'raw' => 'Fonnte token tidak dikonfigurasi',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            $body = $response->json();
            $success = $response->successful() && ($body['status'] ?? false) === true;

            $reference = null;
            if (isset($body['id'])) {
                $reference = is_array($body['id']) ? ($body['id'][0] ?? null) : (string) $body['id'];
            }

            $raw = $success
                ? $body
                : ($body['reason'] ?? $body['message'] ?? (is_array($body) ? json_encode($body) : $response->body()));

            return [
                'success' => $success,
                'reference' => $reference,
                'raw' => $raw,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'reference' => null,
                'raw' => $e->getMessage(),
            ];
        }
    }

    /**
     * Wablas — https://wablas.com
     * Wablas driver.
     *
     * @return array{success: bool, reference: string|null, raw: mixed}
     */
    private function sendViaWablas(string $phone, string $message): array
    {
        $token = config('services.whatsapp.wablas_token');
        $domain = config('services.whatsapp.wablas_domain', 'texas.wablas.com');

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post("https://{$domain}/api/send-message", [
            'phone' => $phone,
            'message' => $message,
        ]);

        $body = $response->json();
        $success = $response->successful() && ($body['status'] ?? false) === true;

        return [
            'success' => $success,
            'reference' => $body['data']['messages'][0]['id'] ?? null,
            'raw' => $body,
        ];
    }

    /**
     * Custom provider — generic POST endpoint.
     * Custom driver.
     *
     * @return array{success: bool, reference: string|null, raw: mixed}
     */
    private function sendViaCustom(string $phone, string $message): array
    {
        $url = config('services.whatsapp.custom_url');
        $token = config('services.whatsapp.custom_token');

        $response = Http::withToken($token)->post($url, [
            'phone' => $phone,
            'message' => $message,
        ]);

        $body = $response->json();

        return [
            'success' => $response->successful(),
            'reference' => $body['id'] ?? $body['reference'] ?? null,
            'raw' => $body,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalisasi nomor telepon ke format internasional Indonesia (628xxx).
     * Mendukung format 08..., 62..., +62..., atau 8...
     */
    public static function normalizeIndonesianPhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Periksa apakah nomor telepon merupakan nomor seluler Indonesia yang valid (format 628xxx dengan 10-15 digit).
     */
    public static function isValidIndonesianPhone(?string $phone): bool
    {
        $normalized = self::normalizeIndonesianPhone($phone);

        if (! $normalized) {
            return false;
        }

        return (bool) preg_match('/^628[0-9]{7,12}$/', $normalized);
    }

    /**
     * Normalisasi nomor telepon ke 62xxx.
     * Returns null if phone is empty or invalid.
     */
    private function normalisePhone(string $phone): ?string
    {
        return self::normalizeIndonesianPhone($phone);
    }
}
