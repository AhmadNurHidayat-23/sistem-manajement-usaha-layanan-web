<?php

namespace App\Models;

use App\Enums\InventoryMutationType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_item_id', 'branch_id', 'user_id', 'type', 'quantity', 'stock_before', 'stock_after', 'reference_type', 'notes'])]
class InventoryMutation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InventoryMutationType::class,
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
