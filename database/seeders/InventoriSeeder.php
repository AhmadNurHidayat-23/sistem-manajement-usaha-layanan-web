<?php

namespace Database\Seeders;

use App\Enums\InventoryMutationType;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoriSeeder extends Seeder
{
    /**
     * Jalankan seeder stok inventori bahan per cabang dan mutasi stok.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();

        $managerA = User::where('email', 'manager@example.com')->firstOrFail();
        $employeeA = User::whereIn('email', ['karyawan@simaul.com', 'employee@example.com'])->firstOrFail();

        // 1. Bahan Cabang A (Pusat - Barbershop)
        $itemA1 = InventoryItem::firstOrCreate(
            ['branch_id' => $branchA->id, 'code' => 'POM-001'],
            [
                'name' => 'Pomade Matte Clay Premium 100g',
                'unit' => 'Jar',
                'current_stock' => 20,
                'minimum_stock' => 5,
                'cost_per_unit' => 65000,
                'description' => 'Produk styling rambut hasil matte natural dengan daya tahan tinggi seharian.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchA->id, 'code' => 'SHP-001'],
            [
                'name' => 'Shampoo Anti-Dandruff & Scalp Care 1L',
                'unit' => 'Botol',
                'current_stock' => 15,
                'minimum_stock' => 4,
                'cost_per_unit' => 55000,
                'description' => 'Shampoo perawatan kulit kepala dan pembersih mendalam rambut pria.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchA->id, 'code' => 'TON-001'],
            [
                'name' => 'Hair Tonic Ginseng & Tea Tree 250ml',
                'unit' => 'Botol',
                'current_stock' => 12,
                'minimum_stock' => 3,
                'cost_per_unit' => 48000,
                'description' => 'Tonik penyegar akar rambut untuk stimulasi pertumbuhan dan nutrisi kulit kepala.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchA->id, 'code' => 'SHV-001'],
            [
                'name' => 'Disposable Razor Blade Pack',
                'unit' => 'Box',
                'current_stock' => 10,
                'minimum_stock' => 3,
                'cost_per_unit' => 45000,
                'description' => 'Mata pisau cukur steril sekali pakai standar higienis barbershop profesional.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchA->id, 'code' => 'CAP-001'],
            [
                'name' => 'Cape Barbershop & Neck Paper',
                'unit' => 'Roll',
                'current_stock' => 25,
                'minimum_stock' => 5,
                'cost_per_unit' => 30000,
                'description' => 'Kertas leher higienis sekali pakai dan jubah pelindung potong rambut.',
            ]
        );

        // 2. Bahan Cabang B (Selatan - Laundry & Shoe Care)
        InventoryItem::firstOrCreate(
            ['branch_id' => $branchB->id, 'code' => 'DET-001'],
            [
                'name' => 'Deterjen Cair Konsentrat Low Foam 5L',
                'unit' => 'Jerigen',
                'current_stock' => 25,
                'minimum_stock' => 5,
                'cost_per_unit' => 85000,
                'description' => 'Deterjen cair ramah mesin cuci bukaan depan, lembut dan efektif mengangkat noda.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchB->id, 'code' => 'SFT-001'],
            [
                'name' => 'Pelembut & Pewangi Pakaian Floral 5L',
                'unit' => 'Jerigen',
                'current_stock' => 20,
                'minimum_stock' => 4,
                'cost_per_unit' => 75000,
                'description' => 'Softener pakaian konsentrat aroma floral segar tahan lama.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchB->id, 'code' => 'FRG-001'],
            [
                'name' => 'Parfum Laundry Premium Anti-Bakteri 1L',
                'unit' => 'Botol',
                'current_stock' => 15,
                'minimum_stock' => 3,
                'cost_per_unit' => 50000,
                'description' => 'Parfum finishing setrika uap antibakterial bebas apek.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchB->id, 'code' => 'SHC-001'],
            [
                'name' => 'Premium Shoe Cleaner & Conditioner 500ml',
                'unit' => 'Botol',
                'current_stock' => 15,
                'minimum_stock' => 3,
                'cost_per_unit' => 75000,
                'description' => 'Cairan pembersih sepatu khusus material canvas, suede, dan kulit asli.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchB->id, 'code' => 'PCK-001'],
            [
                'name' => 'Packaging Laundry Plastik Jinjing Roll',
                'unit' => 'Roll',
                'current_stock' => 30,
                'minimum_stock' => 5,
                'cost_per_unit' => 40000,
                'description' => 'Plastik packaging kemasan laundry berlogo SIMAUL higienis dan tebal.',
            ]
        );

        // 3. Bahan Cabang C (Barat - Auto Care)
        InventoryItem::firstOrCreate(
            ['branch_id' => $branchC->id, 'code' => 'SMP-001'],
            [
                'name' => 'Shampoo Mobil Salju pH Netral 5L',
                'unit' => 'Jerigen',
                'current_stock' => 20,
                'minimum_stock' => 4,
                'cost_per_unit' => 95000,
                'description' => 'Formula busa salju tebal tidak mengikis lapisan wax atau coating cat mobil.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchC->id, 'code' => 'MCF-001'],
            [
                'name' => 'Kain Microfiber 40x40 500 GSM',
                'unit' => 'Pcs',
                'current_stock' => 40,
                'minimum_stock' => 10,
                'cost_per_unit' => 25000,
                'description' => 'Microfiber plush tebal ultra-lembut tanpa swirl marks untuk buffing dan drying.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchC->id, 'code' => 'TIR-001'],
            [
                'name' => 'Tire Dressing Semir Ban Wet Look 1L',
                'unit' => 'Botol',
                'current_stock' => 18,
                'minimum_stock' => 4,
                'cost_per_unit' => 60000,
                'description' => 'Semir ban silikon pekat tahan air memberikan kilau basah elegan dan proteksi UV.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchC->id, 'code' => 'COT-001'],
            [
                'name' => 'Nano Ceramic Coating Liquid 50ml',
                'unit' => 'Botol',
                'current_stock' => 8,
                'minimum_stock' => 2,
                'cost_per_unit' => 250000,
                'description' => 'Cairan nano ceramic 9H hardness pelindung cat tahan gores dan hidrofobik ekstrim.',
            ]
        );

        InventoryItem::firstOrCreate(
            ['branch_id' => $branchC->id, 'code' => 'INT-001'],
            [
                'name' => 'Interior All-Purpose Cleaner 1L',
                'unit' => 'Botol',
                'current_stock' => 16,
                'minimum_stock' => 4,
                'cost_per_unit' => 65000,
                'description' => 'Pembersih interior serbaguna untuk jok kulit, plafon fabric, dan dashboard.',
            ]
        );

        // 4. Mutasi Stok Bahan Cabang A
        InventoryMutation::firstOrCreate(
            [
                'inventory_item_id' => $itemA1->id,
                'notes' => 'Stok awal pembukaan cabang barbershop',
            ],
            [
                'branch_id' => $branchA->id,
                'user_id' => $managerA->id,
                'type' => InventoryMutationType::In,
                'quantity' => 20,
                'stock_before' => 0,
                'stock_after' => 20,
                'reference_type' => 'initial_stock',
            ]
        );

        InventoryMutation::firstOrCreate(
            [
                'inventory_item_id' => $itemA1->id,
                'notes' => 'Penggunaan operasional styling rambut pelanggan',
            ],
            [
                'branch_id' => $branchA->id,
                'user_id' => $employeeA->id,
                'type' => InventoryMutationType::Out,
                'quantity' => 2,
                'stock_before' => 20,
                'stock_after' => 18,
                'reference_type' => 'service_usage',
            ]
        );
    }
}
