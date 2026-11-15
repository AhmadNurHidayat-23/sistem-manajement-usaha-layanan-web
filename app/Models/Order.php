<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'uuid', 'user_id', 'branch_id', 'service_id', 'employee_id',
    'estimated_quantity', 'actual_weight', 'total_amount', 'weighed_at', 'weighed_by',
    'pickup_option', 'delivery_method', 'delivery_selected_at', 'handover_status',
    'estimated_finish_at', 'status', 'notes',
])]
class Order extends Model
{
    use HasFactory, HasPublicUuid;

    protected function casts(): array
    {
        return [
            'estimated_quantity' => 'decimal:2',
            'actual_weight' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'weighed_at' => 'datetime',
            'pickup_option' => 'boolean',
            'delivery_selected_at' => 'datetime',
            'estimated_finish_at' => 'datetime',
            'status' => StatusPesanan::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function weighedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'weighed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function isWeightBased(): bool
    {
        return (bool) ($this->service?->isWeightBased());
    }

    public function isWeighed(): bool
    {
        return ! is_null($this->actual_weight) && ! is_null($this->weighed_at);
    }

    public function isPaid(): bool
    {
        return $this->transaction?->status === StatusTransaksi::Paid;
    }

    public function calculateBill(): float
    {
        if ($this->total_amount && (float) $this->total_amount > 0) {
            return (float) $this->total_amount;
        }

        $servicePrice = (float) ($this->service?->price ?? 0);
        $qty = $this->isWeightBased()
            ? (float) ($this->actual_weight ?: $this->estimated_quantity ?: 1)
            : (float) ($this->estimated_quantity ?: 1);

        $extraSubtotal = (float) $this->details->sum('subtotal');

        return ($servicePrice * $qty) + $extraSubtotal;
    }
}
