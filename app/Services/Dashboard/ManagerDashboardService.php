<?php

namespace App\Services\Dashboard;

use App\Enums\BookingStatus;
use App\Enums\StatusShiftKaryawan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\EmployeeShift;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class ManagerDashboardService
{
    /**
     * Data operasional dashboard manager sesuai 5 kebutuhan SRS.
     *
     * @return array<string, mixed>
     */
    public function getManagerDashboardData(User $manager): array
    {
        $today = Carbon::today();
        $startOfDay = $today->copy()->startOfDay();
        $endOfDay = $today->copy()->endOfDay();
        $startOfMonth = $today->copy()->startOfMonth();

        // Cabang manager (dari relasi user_branches)
        $branch = $manager->branches()->first();

        if (! $branch) {
            return [
                'branch' => null,
                'metrics' => [
                    'today_omzet' => 0,
                    'month_omzet' => 0,
                    'today_transactions' => 0,
                    'month_transactions' => 0,
                    'active_employees_on_duty' => 0,
                ],
                'attendance' => [
                    'is_applicable' => false,
                    'rate' => null,
                    'label' => 'Tidak berlaku',
                    'subtitle' => 'Cabang belum ditugaskan',
                ],
                'inventory_summary' => [
                    'total_items' => 0,
                    'low_stock_count' => 0,
                    'out_of_stock_count' => 0,
                    'total_warning' => 0,
                    'critical_items' => collect(),
                ],
                'today_activities' => collect(),
                'today_shifts' => collect(),
                'recent_transactions' => collect(),
            ];
        }

        // 1. KPI Omzet Cabang (Hari Ini & Bulan Ini)
        $todayTrxs = Transaction::query()
            ->where('branch_id', $branch->id)
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->get();

        $monthTrxsQuery = Transaction::query()
            ->where('branch_id', $branch->id)
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfDay]);

        $monthOmzet = (float) (clone $monthTrxsQuery)->sum('total');
        $monthTransactionsCount = (clone $monthTrxsQuery)->count();

        $todayOmzet = (float) $todayTrxs->sum('total');
        $todayTransactionsCount = $todayTrxs->count();

        // 2. KPI Tingkat Kehadiran Booking (SRS Patokan Wajib)
        // Khusus Laundry (drop_off only): state informatif "Tidak berlaku"
        $supportsAppointment = in_array($branch->service_mode, ['appointment', 'both'], true);

        if ($supportsAppointment) {
            // Dihitung dari booking yang sudah memiliki bukti lifecycle kedatangan/selesai vs batal
            $attendedCount = Booking::where('branch_id', $branch->id)
                ->whereIn('status', [BookingStatus::InProgress, BookingStatus::Completed])
                ->count();

            $cancelledCount = Booking::where('branch_id', $branch->id)
                ->where('status', BookingStatus::Cancelled)
                ->count();

            $evaluableTotal = $attendedCount + $cancelledCount;
            $rate = $evaluableTotal > 0 ? round(($attendedCount / $evaluableTotal) * 100, 1) : null;

            $attendanceData = [
                'is_applicable' => true,
                'rate' => $rate,
                'label' => $rate !== null ? "{$rate}%" : 'Belum ada data',
                'subtitle' => $evaluableTotal > 0
                    ? "{$attendedCount} hadir dari {$evaluableTotal} appointment dievaluasi"
                    : 'Menunggu riwayat appointment terevaluasi',
            ];
        } else {
            $attendanceData = [
                'is_applicable' => false,
                'rate' => null,
                'label' => 'Tidak berlaku',
                'subtitle' => 'Khusus layanan drop-off',
            ];
        }

        // 3. Staf Bertugas Hari Ini
        $activeEmployeesOnDuty = EmployeeShift::query()
            ->where('branch_id', $branch->id)
            ->whereDate('shift_date', $today)
            ->whereIn('status', [StatusShiftKaryawan::Present, StatusShiftKaryawan::Scheduled, StatusShiftKaryawan::Late])
            ->count();

        // 4. Aktivitas Hari Ini (Sesuai service_mode cabang)
        if ($branch->service_mode === 'drop_off') {
            $todayActivities = Order::with(['user', 'service', 'employee'])
                ->where('branch_id', $branch->id)
                ->whereDate('created_at', $today)
                ->latest('created_at')
                ->get();

            if ($todayActivities->isEmpty()) {
                $todayActivities = Order::with(['user', 'service', 'employee'])
                    ->where('branch_id', $branch->id)
                    ->latest('created_at')
                    ->limit(15)
                    ->get();
            }
        } elseif ($branch->service_mode === 'both') {
            $todayBookings = Booking::with(['user', 'service', 'employee'])
                ->where('branch_id', $branch->id)
                ->whereDate('booking_date', $today)
                ->get()
                ->map(function ($b) {
                    $b->activity_type = 'appointment';
                    $b->time_label = $b->booking_time ? Carbon::parse($b->booking_time)->format('H:i') : '-';
                    $b->sort_key = $b->booking_time ?? '00:00:00';

                    return $b;
                });

            $todayOrders = Order::with(['user', 'service', 'employee'])
                ->where('branch_id', $branch->id)
                ->whereDate('created_at', $today)
                ->get()
                ->map(function ($o) {
                    $o->activity_type = 'drop_off';
                    $o->time_label = $o->created_at ? $o->created_at->timezone('Asia/Jakarta')->format('H:i') : '-';
                    $o->sort_key = $o->created_at ? $o->created_at->timezone('Asia/Jakarta')->format('H:i:s') : '00:00:00';

                    return $o;
                });

            $todayActivities = $todayBookings->concat($todayOrders)->sortBy('sort_key')->values();
        } else {
            $todayActivities = Booking::with(['user', 'service', 'employee'])
                ->where('branch_id', $branch->id)
                ->whereDate('booking_date', $today)
                ->orderBy('booking_time')
                ->get();
        }

        // 5. Shift staf cabang hari ini
        $todayShifts = EmployeeShift::with('employee')
            ->where('branch_id', $branch->id)
            ->whereDate('shift_date', $today)
            ->orderBy('start_time')
            ->get();

        // 6. Transaksi kasir terbaru cabang
        $recentTransactions = Transaction::with(['cashier', 'payments'])
            ->where('branch_id', $branch->id)
            ->where('status', StatusTransaksi::Paid)
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        // 7. Ringkasan stok inventori cabang
        $lowStockCount = InventoryItem::where('branch_id', $branch->id)->lowStock()->count();
        $outOfStockCount = InventoryItem::where('branch_id', $branch->id)->outOfStock()->count();
        $totalInventoryItems = InventoryItem::where('branch_id', $branch->id)->count();

        $criticalInventoryItems = InventoryItem::where('branch_id', $branch->id)
            ->where(function ($q) {
                $q->whereColumn('current_stock', '<=', 'minimum_stock');
            })
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        $inventorySummary = [
            'total_items' => $totalInventoryItems,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_warning' => $lowStockCount + $outOfStockCount,
            'critical_items' => $criticalInventoryItems,
        ];

        return [
            'branch' => $branch,
            'metrics' => [
                'today_omzet' => $todayOmzet,
                'month_omzet' => $monthOmzet,
                'today_transactions' => $todayTransactionsCount,
                'month_transactions' => $monthTransactionsCount,
                'active_employees_on_duty' => $activeEmployeesOnDuty,
            ],
            'attendance' => $attendanceData,
            'inventory_summary' => $inventorySummary,
            'today_activities' => $todayActivities,
            'today_shifts' => $todayShifts,
            'recent_transactions' => $recentTransactions,
        ];
    }
}
