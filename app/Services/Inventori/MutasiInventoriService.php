<?php

namespace App\Services\Inventori;

use App\Enums\InventoryMutationType;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MutasiInventoriService
{
    /**
     * Catat stok masuk.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordStockIn(User $user, InventoryItem $item, array $data): InventoryMutation
    {
        $qty = (float) $data['quantity'];
        if ($qty <= 0) {
            throw new HttpException(422, 'Jumlah stok masuk harus lebih dari 0.');
        }

        return DB::transaction(function () use ($user, $item, $qty, $data) {
            $stockBefore = (float) $item->current_stock;
            $stockAfter = $stockBefore + $qty;

            $item->update(['current_stock' => $stockAfter]);

            return InventoryMutation::create([
                'inventory_item_id' => $item->id,
                'branch_id' => $item->branch_id,
                'user_id' => $user->id,
                'type' => InventoryMutationType::In,
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $data['reference_type'] ?? 'restock',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Catat stok keluar.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordStockOut(User $user, InventoryItem $item, array $data): InventoryMutation
    {
        $qty = (float) $data['quantity'];
        if ($qty <= 0) {
            throw new HttpException(422, 'Jumlah stok keluar harus lebih dari 0.');
        }

        if ((float) $item->current_stock < $qty) {
            throw ValidationException::withMessages([
                'quantity' => "Jumlah stok keluar ({$qty}) melebihi stok yang tersedia saat ini ({$item->current_stock} {$item->unit}).",
            ]);
        }

        return DB::transaction(function () use ($user, $item, $qty, $data) {
            $stockBefore = (float) $item->current_stock;
            $stockAfter = $stockBefore - $qty;

            $item->update(['current_stock' => $stockAfter]);

            return InventoryMutation::create([
                'inventory_item_id' => $item->id,
                'branch_id' => $item->branch_id,
                'user_id' => $user->id,
                'type' => InventoryMutationType::Out,
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $data['reference_type'] ?? 'usage',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Riwayat mutasi stok.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getMutationsHistory(User $user, array $filters = [], ?callable $scopeCallback = null): LengthAwarePaginator
    {
        $query = InventoryMutation::with(['inventoryItem', 'branch', 'user']);

        if ($scopeCallback) {
            $query = $scopeCallback($query, $user, $filters['branch_id'] ?? null);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['inventory_item_id'])) {
            $query->where('inventory_item_id', $filters['inventory_item_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->whereHas('inventoryItem', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->latest('id')->paginate(20)->withQueryString();
    }
}
