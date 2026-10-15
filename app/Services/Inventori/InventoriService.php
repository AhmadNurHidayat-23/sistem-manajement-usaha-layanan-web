<?php

namespace App\Services\Inventori;

use App\Enums\InventoryMutationType;
use App\Enums\UserRole;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InventoriService
{
    public function __construct(
        protected MutasiInventoriService $mutationService
    ) {}

    /**
     * Daftar terpaginasi inventory items.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getItems(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = InventoryItem::with('branch');

        $query = $this->scopeToUserBranch($query, $user, $filters['branch_id'] ?? null);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $status = strtolower($filters['status']);
            if ($status === 'tersedia' || $status === 'available') {
                $query->available();
            } elseif ($status === 'menipis' || $status === 'low') {
                $query->lowStock();
            } elseif ($status === 'habis' || $status === 'out_of_stock') {
                $query->outOfStock();
            }
        }

        return $query->orderBy('name')->paginate(15)->withQueryString();
    }

    /**
     * Ringkasan inventori.
     *
     * @param  array<string, mixed>  $filters
     * @return array{total_items: int, available: int, low_stock: int, out_of_stock: int}
     */
    public function getMetrics(User $user, array $filters = []): array
    {
        $baseQuery = InventoryItem::query();
        $baseQuery = $this->scopeToUserBranch($baseQuery, $user, $filters['branch_id'] ?? null);

        $totalItems = (clone $baseQuery)->count();
        $available = (clone $baseQuery)->available()->count();
        $lowStock = (clone $baseQuery)->lowStock()->count();
        $outOfStock = (clone $baseQuery)->outOfStock()->count();

        return [
            'total_items' => $totalItems,
            'available' => $available,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ];
    }

    /**
     * Detail bahan inventori.
     */
    public function getItem(User $user, int $id): InventoryItem
    {
        $item = InventoryItem::with(['branch', 'mutations.user'])->findOrFail($id);

        $this->authorizeItemAccess($user, $item);

        return $item;
    }

    /**
     * Tambah bahan baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createItem(User $user, array $data): InventoryItem
    {
        $branchId = $this->resolveBranchIdForWrite($user, $data['branch_id'] ?? null);

        return DB::transaction(function () use ($user, $data, $branchId) {
            $initialStock = isset($data['initial_stock']) ? (float) $data['initial_stock'] : 0.0;

            $item = InventoryItem::create([
                'branch_id' => $branchId,
                'code' => $data['code'] ?? null,
                'name' => $data['name'],
                'unit' => $data['unit'],
                'current_stock' => $initialStock,
                'minimum_stock' => (float) ($data['minimum_stock'] ?? 5),
                'cost_per_unit' => (float) ($data['cost_per_unit'] ?? 0),
                'description' => $data['description'] ?? null,
            ]);

            if ($initialStock > 0) {
                InventoryMutation::create([
                    'inventory_item_id' => $item->id,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'type' => InventoryMutationType::In,
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reference_type' => 'initial_stock',
                    'notes' => 'Stok awal saat pendaftaran bahan',
                ]);
            }

            return $item;
        });
    }

    /**
     * Perbarui bahan.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(User $user, InventoryItem $item, array $data): InventoryItem
    {
        $this->authorizeItemAccess($user, $item);

        $item->update([
            'code' => $data['code'] ?? $item->code,
            'name' => $data['name'],
            'unit' => $data['unit'],
            'minimum_stock' => (float) $data['minimum_stock'],
            'cost_per_unit' => (float) ($data['cost_per_unit'] ?? 0),
            'description' => $data['description'] ?? null,
        ]);

        return $item;
    }

    /**
     * Catat stok masuk.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordStockIn(User $user, InventoryItem $item, array $data): InventoryMutation
    {
        $this->authorizeItemAccess($user, $item);

        return $this->mutationService->recordStockIn($user, $item, $data);
    }

    /**
     * Catat stok keluar.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordStockOut(User $user, InventoryItem $item, array $data): InventoryMutation
    {
        $this->authorizeItemAccess($user, $item);

        return $this->mutationService->recordStockOut($user, $item, $data);
    }

    /**
     * Riwayat mutasi stok.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getMutationsHistory(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->mutationService->getMutationsHistory($user, $filters, fn ($q, $u, $b) => $this->scopeToUserBranch($q, $u, $b));
    }

    /**
     * Ringkasan stok menipis.
     *
     * @return array{low_stock_count: int, out_of_stock_count: int, total_warning: int, critical_items: Collection<int, InventoryItem>}
     */
    public function getBranchInventorySummary(int $branchId): array
    {
        $lowCount = InventoryItem::where('branch_id', $branchId)->lowStock()->count();
        $outCount = InventoryItem::where('branch_id', $branchId)->outOfStock()->count();

        $criticalItems = InventoryItem::where('branch_id', $branchId)
            ->where(function (Builder $q) {
                $q->whereColumn('current_stock', '<=', 'minimum_stock');
            })
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        return [
            'low_stock_count' => $lowCount,
            'out_of_stock_count' => $outCount,
            'total_warning' => $lowCount + $outCount,
            'critical_items' => $criticalItems,
        ];
    }

    /**
     * Filter cabang sesuai role.
     */
    protected function scopeToUserBranch(Builder $query, User $user, ?int $requestedBranchId = null): Builder
    {
        $role = $user->role?->name;

        if ($role === UserRole::Manager->value) {
            $branch = $user->branches()->first();
            $branchId = $branch ? $branch->id : 0;

            return $query->where('branch_id', $branchId);
        }

        if ($role === UserRole::Owner->value && $requestedBranchId) {
            return $query->where('branch_id', $requestedBranchId);
        }

        return $query;
    }

    /**
     * Otorisasi bahan inventori.
     */
    protected function authorizeItemAccess(User $user, InventoryItem $item): void
    {
        $role = $user->role?->name;

        if ($role === UserRole::Owner->value) {
            return;
        }

        if ($role === UserRole::Manager->value) {
            $branch = $user->branches()->first();
            if ($branch && (int) $branch->id === (int) $item->branch_id) {
                return;
            }
        }

        throw new HttpException(403, 'Anda tidak memiliki hak akses mengelola inventori cabang ini.');
    }

    /**
     * Tentukan cabang tujuan.
     */
    protected function resolveBranchIdForWrite(User $user, ?int $requestedBranchId = null): int
    {
        $role = $user->role?->name;

        if ($role === UserRole::Manager->value) {
            $branch = $user->branches()->first();
            if (! $branch) {
                throw new HttpException(403, 'Akun Manager Anda belum ditugaskan ke cabang manapun.');
            }

            return $branch->id;
        }

        if ($role === UserRole::Owner->value) {
            if (! $requestedBranchId) {
                throw new HttpException(422, 'Pilih cabang outlet untuk inventori ini.');
            }

            return $requestedBranchId;
        }

        throw new HttpException(403, 'Akses ditolak.');
    }
}
