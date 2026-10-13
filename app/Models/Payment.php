<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id',
    'payment_method',
    'channel',
    'amount',
    'status',
    'reference_number',
    'gateway_reference',
    'gateway_payload',
    'paid_at',
    'expired_at',
])]
class Payment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => StatusPembayaran::class,
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Cek pembayaran gateway.
     */
    public function isGatewayPayment(): bool
    {
        return in_array($this->payment_method, ['qris', 'e_wallet', 'virtual_account']);
    }

    public function isGatewayMethod(): bool
    {
        return $this->isGatewayPayment();
    }

    /**
     * Cek status pending.
     */
    public function isPending(): bool
    {
        return $this->status === StatusPembayaran::Pending;
    }

    /**
     * Cek status lunas.
     */
    public function isPaid(): bool
    {
        return in_array($this->status, [StatusPembayaran::Paid, StatusPembayaran::Completed]);
    }

    /**
     * Pengenal publik unik dan aman untuk URL.
     */
    public function getRouteKey(): mixed
    {
        $uuid = $this->relationLoaded('transaction')
            ? $this->transaction?->uuid
            : $this->transaction()->value('uuid');

        if ($uuid) {
            return $uuid;
        }

        $code = $this->relationLoaded('transaction')
            ? $this->transaction?->transaction_code
            : $this->transaction()->value('transaction_code');

        return $code ?: ($this->gateway_reference ?: (string) $this->getKey());
    }
}
