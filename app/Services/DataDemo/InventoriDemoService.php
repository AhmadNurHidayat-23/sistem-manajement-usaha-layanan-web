<?php

namespace App\Services\DataDemo;

use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\User;
use App\Modules\Inventori\Services\MutasiInventoriService;

class InventoriDemoService
{
    public function __construct(
        protected MutasiInventoriService $mutationService
    ) {}

    /**
     * Orkestrasi mutasi inventori realistis (penerimaan stok & pemakaian layanan).
     *
     * @param  (callable(string, string): void)|null  $logger
     */
    public function jalankan(?callable $logger = null): void
    {
        $managerA = User::whereIn('email', ['manajer@simaul.com', 'manager@example.com'])->firstOrFail();
        $managerB = User::whereIn('email', ['manajer.laundry@simaul.com', 'manager.selatan@example.com'])->firstOrFail();
        $managerC = User::whereIn('email', ['manajer.autocare@simaul.com', 'manager.barat@example.com'])->firstOrFail();

        $inventoryItems = InventoryItem::all();

        $mutationDefs = [
            // Cabang A (Pusat - Barbershop)
            ['code' => 'POM-001', 'type' => 'in', 'qty' => 10, 'notes' => 'Restock suplai Pomade Matte dari distributor resmi', 'ref' => 'restock', 'user' => $managerA],
            ['code' => 'POM-001', 'type' => 'out', 'qty' => 3, 'notes' => 'Penggunaan styling potong rambut pelanggan', 'ref' => 'service_usage', 'user' => $managerA],
            ['code' => 'SHP-001', 'type' => 'in', 'qty' => 8, 'notes' => 'Pengadaan shampoo anti-dandruff galon', 'ref' => 'restock', 'user' => $managerA],
            ['code' => 'SHP-001', 'type' => 'out', 'qty' => 2, 'notes' => 'Pemakaian hair spa & keramas', 'ref' => 'service_usage', 'user' => $managerA],
            ['code' => 'TON-001', 'type' => 'in', 'qty' => 6, 'notes' => 'Penerimaan tonik rambut ginseng', 'ref' => 'restock', 'user' => $managerA],
            ['code' => 'TON-001', 'type' => 'out', 'qty' => 1, 'notes' => 'Aplikasi scalp treatment', 'ref' => 'service_usage', 'user' => $managerA],
            ['code' => 'SHV-001', 'type' => 'in', 'qty' => 10, 'notes' => 'Pasokan razor blade steril', 'ref' => 'restock', 'user' => $managerA],
            ['code' => 'SHV-001', 'type' => 'out', 'qty' => 4, 'notes' => 'Pemakaian cukur jenggot', 'ref' => 'service_usage', 'user' => $managerA],

            // Cabang B (Selatan - Laundry & Shoe Care)
            ['code' => 'DET-001', 'type' => 'in', 'qty' => 15, 'notes' => 'Restock deterjen konsentrat bulanan', 'ref' => 'restock', 'user' => $managerB],
            ['code' => 'DET-001', 'type' => 'out', 'qty' => 5, 'notes' => 'Pemakaian operasional mesin cuci 5kg & 10kg', 'ref' => 'service_usage', 'user' => $managerB],
            ['code' => 'SFT-001', 'type' => 'in', 'qty' => 10, 'notes' => 'Stok pelembut pakaian floral', 'ref' => 'restock', 'user' => $managerB],
            ['code' => 'SFT-001', 'type' => 'out', 'qty' => 4, 'notes' => 'Pemakaian finishing cuci kering', 'ref' => 'service_usage', 'user' => $managerB],
            ['code' => 'FRG-001', 'type' => 'in', 'qty' => 8, 'notes' => 'Pengadaan parfum anti-bakteri', 'ref' => 'restock', 'user' => $managerB],
            ['code' => 'FRG-001', 'type' => 'out', 'qty' => 2, 'notes' => 'Spray packaging packing baju', 'ref' => 'service_usage', 'user' => $managerB],
            ['code' => 'SHC-001', 'type' => 'in', 'qty' => 10, 'notes' => 'Restock pembersih sepatu premium', 'ref' => 'restock', 'user' => $managerB],
            ['code' => 'SHC-001', 'type' => 'out', 'qty' => 3, 'notes' => 'Pembersihan 6 pasang sepatu sneakers', 'ref' => 'service_usage', 'user' => $managerB],
            ['code' => 'PCK-001', 'type' => 'out', 'qty' => 5, 'notes' => 'Pengemasan pakaian siap ambil', 'ref' => 'service_usage', 'user' => $managerB],

            // Cabang C (Barat - Auto Care & Detailing)
            ['code' => 'SMP-001', 'type' => 'in', 'qty' => 12, 'notes' => 'Restock shampoo salju car wash', 'ref' => 'restock', 'user' => $managerC],
            ['code' => 'SMP-001', 'type' => 'out', 'qty' => 4, 'notes' => 'Pemakaian pencucian kendaraan roda empat', 'ref' => 'service_usage', 'user' => $managerC],
            ['code' => 'MCF-001', 'type' => 'in', 'qty' => 20, 'notes' => 'Pengadaan kain microfiber 500 GSM baru', 'ref' => 'restock', 'user' => $managerC],
            ['code' => 'MCF-001', 'type' => 'out', 'qty' => 5, 'notes' => 'Distribusi ke detailer bay 1 dan 2', 'ref' => 'service_usage', 'user' => $managerC],
            ['code' => 'TIR-001', 'type' => 'in', 'qty' => 8, 'notes' => 'Restock dressing semir ban', 'ref' => 'restock', 'user' => $managerC],
            ['code' => 'TIR-001', 'type' => 'out', 'qty' => 2, 'notes' => 'Finishing semir ban mobil pelanggan', 'ref' => 'service_usage', 'user' => $managerC],
            ['code' => 'COT-001', 'type' => 'in', 'qty' => 5, 'notes' => 'Pengadaan cairan ceramic nano coating 9H', 'ref' => 'restock', 'user' => $managerC],
            ['code' => 'COT-001', 'type' => 'out', 'qty' => 2, 'notes' => 'Aplikasi full body coating', 'ref' => 'service_usage', 'user' => $managerC],
            ['code' => 'INT-001', 'type' => 'in', 'qty' => 6, 'notes' => 'Restock interior all-purpose cleaner', 'ref' => 'restock', 'user' => $managerC],
            ['code' => 'INT-001', 'type' => 'out', 'qty' => 2, 'notes' => 'Pembersihan interior & jok mobil', 'ref' => 'service_usage', 'user' => $managerC],
        ];

        foreach ($mutationDefs as $def) {
            $item = $inventoryItems->firstWhere('code', $def['code']);
            if (! $item) {
                continue;
            }

            // Pengecekan idempotency: jangan duplikasi mutasi jika notes sudah ada
            $alreadyRecorded = InventoryMutation::where('inventory_item_id', $item->id)
                ->where('notes', $def['notes'])
                ->exists();

            if ($alreadyRecorded) {
                continue;
            }

            try {
                if ($def['type'] === 'in') {
                    $this->mutationService->recordStockIn($def['user'], $item, [
                        'quantity' => $def['qty'],
                        'notes' => $def['notes'],
                        'reference_type' => $def['ref'],
                    ]);
                } else {
                    $this->mutationService->recordStockOut($def['user'], $item, [
                        'quantity' => $def['qty'],
                        'notes' => $def['notes'],
                        'reference_type' => $def['ref'],
                    ]);
                }
            } catch (\Throwable $e) {
                if ($logger) {
                    $logger('warn', "Mutasi {$def['code']} ({$def['type']}) dilewati: {$e->getMessage()}");
                }
            }
        }
    }
}
