<?php

namespace App\Models;

use App\Enums\InventoryStockStatus;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['uuid', 'branch_id', 'code', 'name', 'unit', 'current_stock', 'minimum_stock', 'cost_per_unit', 'description'])]
class InventoryItem extends Model
{
    use HasFactory, HasPublicUuid;

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(InventoryMutation::class)->latest('id');
    }

    /**
     * Compute current stock status: tersedia, menipis, or habis.
     */
    public function getStatusAttribute(): string
    {
        if ((float) $this->current_stock <= 0) {
            return InventoryStockStatus::OutOfStock->value;
        }

        if ((float) $this->current_stock <= (float) $this->minimum_stock) {
            return InventoryStockStatus::Low->value;
        }

        return InventoryStockStatus::Available->value;
    }

    /**
     * Label status stok.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            InventoryStockStatus::OutOfStock->value => 'Habis',
            InventoryStockStatus::Low->value => 'Menipis',
            default => 'Tersedia',
        };
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '>', 'minimum_stock');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('current_stock', '<=', 0);
    }
}
