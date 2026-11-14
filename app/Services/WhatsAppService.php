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

        $targetPhone = $this->resolveRecipientPhone($phone);

        return match ($this->driver) {
            'baileys' => $this->sendViaBaileys($targetPhone, $message),
            'fonnte' => $this->sendViaFonnte($targetPhone, $message),
            'wablas' => $this->sendViaWablas($targetPhone, $message),
            'custom' => $this->sendViaCustom($targetPhone, $message),
            default => $this->sendViaLog($targetPhone, $message),
        };
    }

    /**
     * Nama driver aktif.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Periksa status koneksi gateway WhatsApp yang sedang aktif.
     *
     * @return array{success: bool, connected: bool, driver: string, status: string, has_qr: bool, qr_url?: string, message: string}
     */
    public function checkConnection(): array
    {
        return match ($this->driver) {
            'baileys' => $this->checkBaileysConnection(),
            'fonnte' => $this->checkFonnteConnection(),
            'wablas' => $this->checkWablasConnection(),
            'log' => [
                'success' => true,
                'connected' => true,
                'driver' => 'log',
                'status' => 'connected',
                'has_qr' => false,
                'message' => 'Driver log aktif (pesan dicatat ke Laravel log stack).',
            ],
            default => [
                'success' => false,
                'connected' => false,
                'driver' => $this->driver,
                'status' => 'unknown',
                'has_qr' => false,
                'message' => "Driver '{$this->driver}' belum memiliki pengecekan koneksi otomatis.",
            ],
        };
    }

    /**
     * Alihkan nomor penerima jika mode pengujian WHATSAPP_TEST_NUMBER aktif.
     */
    public function resolveRecipientPhone(string $phone): string
    {
        $testNumber = config('services.whatsapp.test_number');
        if (! empty($testNumber)) {
            $normalizedTestNumber = self::normalizeIndonesianPhone((string) $testNumber);
            if ($normalizedTestNumber) {
                return $normalizedTestNumber;
            }
        }

        return $phone;
    }

    /**
     * Masking nomor telepon untuk logging yang aman.
     */
    public static function maskPhone(?string $phone): string
    {
        if (! $phone) {
            return '-';
        }

        $len = strlen($phone);
        if ($len <= 6) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 4).str_repeat('*', max(3, $len - 7)).substr($phone, -3);
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
                'to' => self::maskPhone($phone),
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
            'to' => self::maskPhone($phone),
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

    /**
     * Pengecekan koneksi ke Baileys local WhatsApp gateway.
     *
     * @return array{success: bool, connected: bool, driver: string, status: string, has_qr: bool, qr_url?: string, message: string}
     */
    private function checkBaileysConnection(): array
    {
        $baseUrl = rtrim((string) config('services.whatsapp.baileys_url', 'http://127.0.0.1:3001'), '/');

        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->acceptJson()
                ->get("{$baseUrl}/health");

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'connected' => false,
                    'driver' => 'baileys',
                    'status' => 'error',
                    'has_qr' => false,
                    'message' => "Gateway Baileys merespons dengan HTTP status {$response->status()}.",
                ];
            }

            $data = $response->json();
            $connected = ($data['connected'] ?? false) === true;
            $hasQr = ($data['hasQr'] ?? false) === true;

            return [
                'success' => true,
                'connected' => $connected,
                'driver' => 'baileys',
                'status' => $connected ? 'connected' : 'disconnected',
                'has_qr' => $hasQr,
                'qr_url' => "{$baseUrl}/qr",
                'message' => $connected
                    ? 'WhatsApp Gateway Baileys terhubung dan siap mengirim pesan.'
                    : "WhatsApp Gateway Baileys aktif tetapi belum terhubung. Silakan scan QR code di {$baseUrl}/qr",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'connected' => false,
                'driver' => 'baileys',
                'status' => 'offline',
                'has_qr' => false,
                'qr_url' => "{$baseUrl}/qr",
                'message' => "WhatsApp Gateway Baileys offline / tidak dapat dihubungi di {$baseUrl} ({$e->getMessage()}).",
            ];
        }
    }

    /**
     * Pengecekan status Fonnte.
     *
     * @return array{success: bool, connected: bool, driver: string, status: string, has_qr: bool, message: string}
     */
    private function checkFonnteConnection(): array
    {
        $token = config('services.whatsapp.fonnte_token');

        if (! $token) {
            return [
                'success' => false,
                'connected' => false,
                'driver' => 'fonnte',
                'status' => 'unconfigured',
                'has_qr' => false,
                'message' => 'Fonnte token belum dikonfigurasi di .env (WHATSAPP_FONNTE_TOKEN).',
            ];
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/device');

            $body = $response->json();
            $deviceStatus = $body['device_status'] ?? null;
            $connected = ($deviceStatus === 'connect');

            return [
                'success' => $response->successful(),
                'connected' => $connected,
                'driver' => 'fonnte',
                'status' => $connected ? 'connected' : 'disconnected',
                'has_qr' => false,
                'message' => $connected
                    ? 'Fonnte terhubung.'
                    : 'Fonnte terdaftar tetapi device belum terhubung: '.($body['reason'] ?? $deviceStatus ?? 'disconnected'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'connected' => false,
                'driver' => 'fonnte',
                'status' => 'error',
                'has_qr' => false,
                'message' => 'Gagal menghubungi server Fonnte: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Pengecekan status Wablas.
     *
     * @return array{success: bool, connected: bool, driver: string, status: string, has_qr: bool, message: string}
     */
    private function checkWablasConnection(): array
    {
        $token = config('services.whatsapp.wablas_token');
        $domain = config('services.whatsapp.wablas_domain', 'texas.wablas.com');

        if (! $token) {
            return [
                'success' => false,
                'connected' => false,
                'driver' => 'wablas',
                'status' => 'unconfigured',
                'has_qr' => false,
                'message' => 'Wablas token belum dikonfigurasi di .env (WHATSAPP_WABLAS_TOKEN).',
            ];
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Authorization' => $token])
                ->get("https://{$domain}/api/device/info");

            $body = $response->json();
            $status = $body['data']['status'] ?? null;
            $connected = ($status === 'connected');

            return [
                'success' => $response->successful(),
                'connected' => $connected,
                'driver' => 'wablas',
                'status' => $connected ? 'connected' : 'disconnected',
                'has_qr' => false,
                'message' => $connected ? 'Wablas terhubung.' : 'Wablas device status: '.($status ?? 'unknown'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'connected' => false,
                'driver' => 'wablas',
                'status' => 'error',
                'has_qr' => false,
                'message' => 'Gagal menghubungi server Wablas: '.$e->getMessage(),
            ];
        }
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
