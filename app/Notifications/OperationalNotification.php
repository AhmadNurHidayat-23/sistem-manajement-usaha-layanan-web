<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OperationalNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $category,
        public string $statusLevel = 'info',
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public array $details = [],
        public ?string $idempotencyKey = null,
    ) {}

    /**
     * Kanal notifikasi: database SIMAUL.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Representasi array data notifikasi tersimpan.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'category' => $this->category,
            'status_level' => $this->statusLevel,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
            'details' => $this->details,
            'idempotency_key' => $this->idempotencyKey,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
