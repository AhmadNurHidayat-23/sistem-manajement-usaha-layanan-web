<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class KirimNotifikasiWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan saat gagal.
     */
    public int $tries = 3;

    /**
     * Jeda waktu antar percobaan dalam detik.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $userId,
        public string $phone,
        public string $type,
        public string $message,
        public ?string $notifiableType = null,
        public ?int $notifiableId = null,
        public ?int $logId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsApp): void
    {
        $log = $this->logId ? NotificationLog::find($this->logId) : null;

        if (! $log) {
            $log = new NotificationLog([
                'user_id' => $this->userId,
                'channel' => 'whatsapp',
                'type' => $this->type,
                'recipient' => $this->phone,
                'message' => $this->message,
                'status' => 'pending',
                'notifiable_type' => $this->notifiableType,
                'notifiable_id' => $this->notifiableId,
                'created_at' => now(),
            ]);
            $log->save();
        }

        try {
            $result = $whatsApp->send($this->phone, $this->message);

            $log->status = $result['success'] ? 'sent' : 'failed';
            $log->error = $result['success'] ? null : (string) ($result['raw'] ?? 'Unknown error');
            $log->provider_reference = $result['reference'] ?? null;
            $log->sent_at = $result['success'] ? now() : null;
        } catch (\Throwable $e) {
            $log->status = 'failed';
            $log->error = $e->getMessage();

            Log::error('[KirimNotifikasiWhatsAppJob] Gagal mengirim pesan', [
                'type' => $this->type,
                'phone' => WhatsAppService::maskPhone($this->phone),
                'error' => $e->getMessage(),
            ]);
        }

        $log->save();
    }
}
