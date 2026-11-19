<?php

namespace App\Services\Inventori;

use App\Enums\InventoryMutationType;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoriTransaksiService
{
    /**
     * Kurangi stok inventori secara otomatis saat transaksi kasir / layanan berstatus Paid.
     * Mencegah pemotongan ganda (idempotent), stok negatif, dan mutasi tanpa referensi.
     */
    public function deductStockForTransaction(Transaction $transaction): void
    {
        // 1. Cek idempotency: pastikan tidak terjadi pemotongan stok ganda untuk transaksi yang sama
        $tag = "[TRX:{$transaction->transaction_code}]";
        $alreadyDeducted = InventoryMutation::where('type', InventoryMutationType::Out)
            ->where('notes', 'like', "%{$tag}%")
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        $transaction->loadMissing(['details.service', 'cashier', 'employee.user', 'branch']);

        if ($transaction->details->isEmpty()) {
            return;
        }

        // Tentukan user petugas yang mencatat mutasi
        $operatorUser = $transaction->cashier
            ?? $transaction->employee?->user
            ?? auth()->user()
            ?? User::whereHas('role', fn ($q) => $q->whereIn('name', ['cashier', 'manager']))->first()
            ?? User::first();

        if (! $operatorUser) {
            Log::warning("[InventoriTransaksiService] Tidak dapat menemukan user operator untuk transaksi {$transaction->transaction_code}");

            return;
        }

        DB::transaction(function () use ($transaction, $operatorUser, $tag) {
            // Re-check dalam transaksi database untuk mencegah race condition
            $alreadyDeductedInLock = InventoryMutation::where('type', InventoryMutationType::Out)
                ->where('notes', 'like', "%{$tag}%")
                ->exists();

            if ($alreadyDeductedInLock) {
                return;
            }

            foreach ($transaction->details as $detail) {
                $service = $detail->service;
                $serviceQty = (float) $detail->qty;

                if (! $service || $serviceQty <= 0) {
                    continue;
                }

                // Cek 1: Apakah ini penjualan barang langsung di POS (nama/kode cocok dengan inventory item pada cabang ini)
                $directItem = InventoryItem::where('branch_id', $transaction->branch_id)
                    ->where(function ($q) use ($service) {
                        $q->where('name', $service->name);
                        if (! empty($service->code)) {
                            $q->orWhere('code', $service->code);
                        }
                    })
                    ->first();

                if ($directItem) {
                    $this->deductItemStock(
                        item: $directItem,
                        quantityToDeduct: $serviceQty,
                        user: $operatorUser,
                        transaction: $transaction,
                        referenceType: 'pos_sale',
                        description: "Penjualan barang POS: {$directItem->name} x {$serviceQty} {$directItem->unit}"
                    );

                    continue;
                }

                // Cek 2: Pemakaian bahan operasional yang dikonfigurasi untuk layanan terkait
                $materialRecipes = $this->getOperationalMaterialConfig($service, $serviceQty, $transaction->branch_id);

                // Jika belum ada konfigurasi bahan operasional untuk layanan ini, jangan memotong stok secara sembarangan
                if (empty($materialRecipes)) {
                    continue;
                }

                foreach ($materialRecipes as $recipe) {
                    $item = $recipe['item'];
                    $amount = (float) $recipe['quantity'];

                    if ($amount <= 0 || ! $item) {
                        continue;
                    }

                    $this->deductItemStock(
                        item: $item,
                        quantityToDeduct: $amount,
                        user: $operatorUser,
                        transaction: $transaction,
                        referenceType: 'service_usage',
                        description: "Pemakaian operasional layanan {$service->name} ({$amount} {$item->unit})"
                    );
                }
            }
        });
    }

    /**
     * Kembalikan stok saat transaksi dibatalkan atau direfund.
     * Mencegah pengembalian ganda (idempotent).
     */
    public function reverseStockForTransaction(Transaction $transaction): void
    {
        $trxTag = "[TRX:{$transaction->transaction_code}]";
        $revTag = "[REVERSAL:{$transaction->transaction_code}]";

        // Cek apakah reversal sudah pernah dilakukan
        $alreadyReversed = InventoryMutation::where('type', InventoryMutationType::In)
            ->where('notes', 'like', "%{$revTag}%")
            ->exists();

        if ($alreadyReversed) {
            return;
        }

        // Ambil semua mutasi keluar yang dicatat oleh transaksi ini
        $deductedMutations = InventoryMutation::where('type', InventoryMutationType::Out)
            ->where('notes', 'like', "%{$trxTag}%")
            ->get();

        if ($deductedMutations->isEmpty()) {
            return;
        }

        $operatorUser = $transaction->cashier
            ?? $transaction->employee?->user
            ?? auth()->user()
            ?? User::whereHas('role', fn ($q) => $q->whereIn('name', ['cashier', 'manager']))->first()
            ?? User::first();

        if (! $operatorUser) {
            return;
        }

        DB::transaction(function () use ($deductedMutations, $transaction, $operatorUser, $revTag) {
            // Re-check dalam transaksi
            $alreadyReversedInLock = InventoryMutation::where('type', InventoryMutationType::In)
                ->where('notes', 'like', "%{$revTag}%")
                ->exists();

            if ($alreadyReversedInLock) {
                return;
            }

            foreach ($deductedMutations as $mutation) {
                $item = InventoryItem::find($mutation->inventory_item_id);
                if (! $item) {
                    continue;
                }

                $qty = (float) $mutation->quantity;
                if ($qty <= 0) {
                    continue;
                }

                $stockBefore = (float) $item->current_stock;
                $stockAfter = $stockBefore + $qty;

                $item->update(['current_stock' => $stockAfter]);

                InventoryMutation::create([
                    'inventory_item_id' => $item->id,
                    'branch_id' => $item->branch_id,
                    'user_id' => $operatorUser->id,
                    'type' => InventoryMutationType::In,
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'refund',
                    'notes' => "Pengembalian stok pembatalan transaksi {$transaction->transaction_code} ({$item->name}) {$revTag}",
                ]);
            }
        });
    }

    /**
     * Potong stok satu item inventori dengan pengamanan ketat (mencegah stok negatif).
     */
    protected function deductItemStock(
        InventoryItem $item,
        float $quantityToDeduct,
        User $user,
        Transaction $transaction,
        string $referenceType,
        string $description
    ): ?InventoryMutation {
        $stockBefore = (float) $item->current_stock;

        // Cegah stok menjadi negatif secara tidak wajar
        if ($stockBefore <= 0) {
            Log::warning("[InventoriTransaksiService] Stok {$item->name} sudah habis (0), pemotongan stok otomatis dilewati untuk transaksi {$transaction->transaction_code}.");

            return null;
        }

        // Jika kuantitas yang diminta melebihi stok yang ada, potong maksimal sampai 0 (tidak negatif)
        $actualQty = min($stockBefore, $quantityToDeduct);
        if ($actualQty <= 0) {
            return null;
        }

        $stockAfter = max(0.0, $stockBefore - $actualQty);
        $item->update(['current_stock' => $stockAfter]);

        $tag = "[TRX:{$transaction->transaction_code}]";
        $notes = "{$description} {$tag}";

        return InventoryMutation::create([
            'inventory_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'user_id' => $user->id,
            'type' => InventoryMutationType::Out,
            'quantity' => $actualQty,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'notes' => $notes,
        ]);
    }

    /**
     * Dapatkan konfigurasi pemakaian bahan operasional untuk layanan terkait.
     * Mengembalikan list array ['item' => InventoryItem, 'quantity' => float].
     *
     * @return array<int, array{item: InventoryItem, quantity: float}>
     */
    protected function getOperationalMaterialConfig(Service $service, float $serviceQty, int $branchId): array
    {
        $name = strtolower($service->name);
        $recipes = [];

        // Mapping aman berdasarkan kata kunci layanan dan item cabang terkait
        // Barbershop (Cabang 1)
        if (str_contains($name, 'styling')) {
            $item = $this->findItemByCodeOrName($branchId, 'POM-001', 'Pomade');
            if ($item) {
                $recipes[] = ['item' => $item, 'quantity' => 0.1 * $serviceQty];
            }
            $cape = $this->findItemByCodeOrName($branchId, 'CAP-001', 'Cape');
            if ($cape) {
                $recipes[] = ['item' => $cape, 'quantity' => 0.05 * $serviceQty];
            }
        } elseif (str_contains($name, 'potong rambut') || str_contains($name, 'haircut')) {
            $cape = $this->findItemByCodeOrName($branchId, 'CAP-001', 'Cape');
            if ($cape) {
                $recipes[] = ['item' => $cape, 'quantity' => 0.05 * $serviceQty];
            }
        }

        if (str_contains($name, 'jenggot') || str_contains($name, 'cukur')) {
            $razor = $this->findItemByCodeOrName($branchId, 'SHV-001', 'Razor');
            if ($razor) {
                $recipes[] = ['item' => $razor, 'quantity' => 1.0 * $serviceQty];
            }
        }

        if (str_contains($name, 'kulit kepala') || str_contains($name, 'scalp')) {
            $tonic = $this->findItemByCodeOrName($branchId, 'TON-001', 'Tonic');
            if ($tonic) {
                $recipes[] = ['item' => $tonic, 'quantity' => 0.1 * $serviceQty];
            }
            $shampoo = $this->findItemByCodeOrName($branchId, 'SHP-001', 'Shampoo');
            if ($shampoo) {
                $recipes[] = ['item' => $shampoo, 'quantity' => 0.1 * $serviceQty];
            }
        }

        if (str_contains($name, 'pewarnaan') || str_contains($name, 'colouring') || str_contains($name, 'coloring')) {
            $color = $this->findItemByCodeOrName($branchId, null, 'Pewarna');
            if ($color) {
                $recipes[] = ['item' => $color, 'quantity' => 1.0 * $serviceQty];
            }
        }

        // Laundry & Shoe Care (Cabang 2)
        if (str_contains($name, 'cuci kering') || str_contains($name, 'laundry express')) {
            $det = $this->findItemByCodeOrName($branchId, 'DET-001', 'Deterjen');
            if ($det) {
                $recipes[] = ['item' => $det, 'quantity' => 0.05 * $serviceQty];
            }
            $soft = $this->findItemByCodeOrName($branchId, 'SFT-001', 'Pelembut');
            if ($soft) {
                $recipes[] = ['item' => $soft, 'quantity' => 0.03 * $serviceQty];
            }
            $pck = $this->findItemByCodeOrName($branchId, 'PCK-001', 'Packaging');
            if ($pck) {
                $recipes[] = ['item' => $pck, 'quantity' => 1.0];
            }
        }

        if (str_contains($name, 'sepatu') || str_contains($name, 'shoe')) {
            $cleaner = $this->findItemByCodeOrName($branchId, 'SHC-001', 'Shoe');
            if ($cleaner) {
                $recipes[] = ['item' => $cleaner, 'quantity' => 0.1 * $serviceQty];
            }
        }

        // Auto Care (Cabang 3)
        if (str_contains($name, 'cuci mobil')) {
            $smp = $this->findItemByCodeOrName($branchId, 'SMP-001', 'Shampoo Mobil');
            if ($smp) {
                $recipes[] = ['item' => $smp, 'quantity' => 0.15 * $serviceQty];
            }
            $tire = $this->findItemByCodeOrName($branchId, 'TIR-001', 'Tire');
            if ($tire) {
                $recipes[] = ['item' => $tire, 'quantity' => 0.05 * $serviceQty];
            }
        }

        if (str_contains($name, 'detailing interior')) {
            $int = $this->findItemByCodeOrName($branchId, 'INT-001', 'Interior');
            if ($int) {
                $recipes[] = ['item' => $int, 'quantity' => 0.1 * $serviceQty];
            }
        }

        if (str_contains($name, 'coating')) {
            $cot = $this->findItemByCodeOrName($branchId, 'COT-001', 'Coating');
            if ($cot) {
                $recipes[] = ['item' => $cot, 'quantity' => 1.0 * $serviceQty];
            }
        }

        return $recipes;
    }

    /**
     * Cari bahan inventori pada cabang tertentu berdasarkan kode atau potongan nama.
     */
    protected function findItemByCodeOrName(int $branchId, ?string $code, string $namePart): ?InventoryItem
    {
        $query = InventoryItem::where('branch_id', $branchId);

        if (! empty($code)) {
            $item = (clone $query)->where('code', $code)->first();
            if ($item) {
                return $item;
            }
        }

        return $query->where('name', 'like', "%{$namePart}%")->first();
    }
}
