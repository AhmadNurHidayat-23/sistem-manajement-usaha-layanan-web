<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\StatusShiftKaryawan;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoManagerSeeder extends Seeder
{
    /**
     * Seeder data demo operasional khusus 3 unit usaha SIMAUL.
     * Idempoten, aman, dan mematuhi isolasi data branch.
     */
    public function run(): void
    {
        $today = Carbon::today();
        $todayStr = $today->toDateString();

        // 1. Pastikan penugasan akun Manajer per usaha tepat di tabel user_branches
        $managerAssignments = [
            1 => ['manajer@simaul.com', 'manager@example.com'],          // Barbershop & Studio
            2 => ['manajer.laundry@simaul.com', 'manager.selatan@example.com'],  // Laundry & Shoe Care
            3 => ['manajer.autocare@simaul.com', 'manager.barat@example.com'],    // Auto Care & Detailing
        ];

        foreach ($managerAssignments as $branchId => $emails) {
            $user = User::whereIn('email', (array) $emails)->first();
            if ($user) {
                DB::table('user_branches')->updateOrInsert(
                    ['user_id' => $user->id, 'branch_id' => $branchId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 2. Promo status 'Pending' per cabang untuk pengujian alur persetujuan Owner
        $pendingPromos = [
            [
                'branch_id' => 1,
                'code' => 'BARBERNEW',
                'name' => 'Diskon Pelanggan Baru Barbershop',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 15,
                'quota' => 50,
                'start_date' => $todayStr,
                'end_date' => $today->copy()->addDays(30)->toDateString(),
                'status' => PromotionStatus::Pending,
            ],
            [
                'branch_id' => 2,
                'code' => 'LAUNDRYKILAT',
                'name' => 'Promo Cuci Kilat Akhir Pekan',
                'discount_type' => DiscountType::Fixed,
                'discount_value' => 10000,
                'quota' => 30,
                'start_date' => $todayStr,
                'end_date' => $today->copy()->addDays(30)->toDateString(),
                'status' => PromotionStatus::Pending,
            ],
            [
                'branch_id' => 3,
                'code' => 'AUTODETAIL',
                'name' => 'Voucher Detailing Mobil Spesial',
                'discount_type' => DiscountType::Percentage,
                'discount_value' => 20,
                'quota' => 25,
                'start_date' => $todayStr,
                'end_date' => $today->copy()->addDays(30)->toDateString(),
                'status' => PromotionStatus::Pending,
            ],
        ];

        foreach ($pendingPromos as $p) {
            Promotion::updateOrCreate(
                ['code' => $p['code']],
                $p
            );
        }

        // 3. Item bahan dengan stok menipis per cabang untuk menguji alert ketersediaan stok
        $lowStockItems = [
            1 => [
                'name' => 'Pewarna Rambut Ash Grey 100ml',
                'unit' => 'Botol',
                'current_stock' => 2,
                'minimum_stock' => 5,
            ],
            2 => [
                'name' => 'Cairan Pembersih Khusus Sepatu Suede 250ml',
                'unit' => 'Botol',
                'current_stock' => 1,
                'minimum_stock' => 4,
            ],
            3 => [
                'name' => 'Quick Detailer Spray Wax 500ml',
                'unit' => 'Botol',
                'current_stock' => 2,
                'minimum_stock' => 6,
            ],
        ];

        $owner = User::where('role_id', 1)->first() ?? User::first();

        foreach ($lowStockItems as $branchId => $data) {
            $item = InventoryItem::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'name' => $data['name'],
                ],
                [
                    'unit' => $data['unit'],
                    'current_stock' => $data['current_stock'],
                    'minimum_stock' => $data['minimum_stock'],
                ]
            );

            // Pastikan stok tetap berada di bawah minimum untuk pengujian dashboard
            if ($item->current_stock > $item->minimum_stock) {
                $item->update(['current_stock' => $data['current_stock']]);
            }

            // Catat contoh mutasi awal jika belum ada
            if ($item->mutations()->count() === 0 && $owner) {
                InventoryMutation::create([
                    'branch_id' => $branchId,
                    'inventory_item_id' => $item->id,
                    'user_id' => $owner->id,
                    'type' => 'in',
                    'quantity' => 10,
                    'stock_before' => 0,
                    'stock_after' => 10,
                    'notes' => 'Stok awal demo',
                ]);
                InventoryMutation::create([
                    'branch_id' => $branchId,
                    'inventory_item_id' => $item->id,
                    'user_id' => $owner->id,
                    'type' => 'out',
                    'quantity' => 10 - $data['current_stock'],
                    'stock_before' => 10,
                    'stock_after' => $data['current_stock'],
                    'notes' => 'Penggunaan operasional harian (stok menipis)',
                ]);
            }
        }

        // 4. Jadwal shift hari ini & mendatang per cabang (maksimal 8 jam, tidak bentrok)
        $shiftTimes = [
            1 => ['start' => '09:00:00', 'end' => '17:00:00'], // 8 jam
            2 => ['start' => '08:00:00', 'end' => '16:00:00'], // 8 jam
            3 => ['start' => '08:30:00', 'end' => '16:30:00'], // 8 jam
        ];

        for ($bId = 1; $bId <= 3; $bId++) {
            $emp = Employee::where('branch_id', $bId)->where('status', 'active')->first();
            if (! $emp) {
                continue;
            }

            $time = $shiftTimes[$bId];

            // Shift hari ini
            EmployeeShift::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'branch_id' => $bId,
                    'shift_date' => $todayStr,
                ],
                [
                    'start_time' => $time['start'],
                    'end_time' => $time['end'],
                    'status' => StatusShiftKaryawan::Scheduled->value,
                ]
            );

            // Shift besok
            EmployeeShift::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'branch_id' => $bId,
                    'shift_date' => $today->copy()->addDay()->toDateString(),
                ],
                [
                    'start_time' => $time['start'],
                    'end_time' => $time['end'],
                    'status' => StatusShiftKaryawan::Scheduled->value,
                ]
            );
        }
    }
}
