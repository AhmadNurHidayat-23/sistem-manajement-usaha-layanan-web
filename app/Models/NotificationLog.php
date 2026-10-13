<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    public $timestamps = false;

    protected $table = 'notification_logs';

    protected $fillable = [
        'user_id',
        'channel',
        'type',
        'recipient',
        'message',
        'status',
        'error',
        'notifiable_type',
        'notifiable_id',
        'provider_reference',
        'sent_at',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Whether this notification was delivered successfully.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'sent';
    }
}
