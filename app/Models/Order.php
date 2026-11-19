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
    'pickup_option', 'pickup_address', 'pickup_latitude', 'pickup_longitude',
    'different_delivery_location', 'delivery_address', 'delivery_latitude', 'delivery_longitude', 'location_notes',
    'delivery_method', 'delivery_selected_at', 'handover_status',
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
            'pickup_latitude' => 'float',
            'pickup_longitude' => 'float',
            'different_delivery_location' => 'boolean',
            'delivery_latitude' => 'float',
            'delivery_longitude' => 'float',
            'delivery_selected_at' => 'datetime',
            'estimated_finish_at' => 'datetime',
            'status' => StatusPesanan::class,
        ];
    }

    public function getGoogleMapsPickupUrlAttribute(): ?string
    {
        if ($this->pickup_latitude && $this->pickup_longitude) {
            return "https://www.google.com/maps/dir/?api=1&destination={$this->pickup_latitude},{$this->pickup_longitude}";
        }
        if ($this->pickup_address) {
            return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->pickup_address);
        }

        return null;
    }

    public function getGoogleMapsDeliveryUrlAttribute(): ?string
    {
        $lat = $this->different_delivery_location ? $this->delivery_latitude : $this->pickup_latitude;
        $lng = $this->different_delivery_location ? $this->delivery_longitude : $this->pickup_longitude;
        $address = $this->different_delivery_location ? $this->delivery_address : $this->pickup_address;

        if ($lat && $lng) {
            return "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
        }
        if ($address) {
            return 'https://www.google.com/maps/search/?api=1&query='.urlencode($address);
        }

        return null;
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
