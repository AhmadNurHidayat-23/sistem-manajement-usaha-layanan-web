<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Models\Branch;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    /**
     * Jalankan seeder promosi dan voucher diskon global dan per cabang.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();

        // 1. Promo Persentase Semua Cabang (Global)
        Promotion::updateOrCreate(
            ['code' => 'SIMAUL10'],
            [
                'branch_id' => null,
                'name' => 'Diskon Promo Pembukaan 10%',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 10,
                'quota' => 100,
                'start_date' => Carbon::yesterday()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->addDays(60)->toDateString(),
                'status' => PromotionStatus::Active,
            ]
        );

        // Keep DISKON10 for backward compatibility
        Promotion::updateOrCreate(
            ['code' => 'DISKON10'],
            [
                'branch_id' => null,
                'name' => 'Diskon Pelanggan Setia 10%',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 10,
                'quota' => 100,
                'start_date' => Carbon::yesterday()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->addDays(60)->toDateString(),
                'status' => PromotionStatus::Active,
            ]
        );

        // 2. Promo Khusus Cabang A (Pusat - Barbershop)
        Promotion::updateOrCreate(
            ['code' => 'BARBER15K'],
            [
                'branch_id' => $branchA->id,
                'name' => 'Potongan Rp 15.000 Barbershop Pusat',
                'discount_type' => DiscountType::Fixed,
                'discount_value' => 15000,
                'quota' => 50,
                'start_date' => Carbon::yesterday()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->addDays(60)->toDateString(),
                'status' => PromotionStatus::Active,
            ]
        );

        // 3. Promo Khusus Cabang B (Selatan - Laundry & Shoes)
        Promotion::updateOrCreate(
            ['code' => 'LAUNDRY10'],
            [
                'branch_id' => $branchB->id,
                'name' => 'Diskon Laundry & Cuci Sepatu 10%',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 10,
                'quota' => 75,
                'start_date' => Carbon::yesterday()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->addDays(60)->toDateString(),
                'status' => PromotionStatus::Active,
            ]
        );

        // 4. Promo Khusus Cabang C (Barat - Auto Care & Detailing)
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();
        Promotion::updateOrCreate(
            ['code' => 'AUTOCARE20'],
            [
                'branch_id' => $branchC->id,
                'name' => 'Diskon Auto Care & Detailing 20%',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 20,
                'quota' => 50,
                'start_date' => Carbon::yesterday()->subDays(30)->toDateString(),
                'end_date' => Carbon::today()->addDays(60)->toDateString(),
                'status' => PromotionStatus::Active,
            ]
        );
    }
}
