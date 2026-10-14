<?php

namespace App\Services\Dashboard;

use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\PromotionUsage;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class OwnerDashboardService
{
    /**
     * Data analitik bisnis dashboard owner.
     *
     * @return array<string, mixed>
     */
    public function getOwnerDashboardData(): array
    {
        $today = Carbon::today();
        $endOfDay = $today->copy()->endOfDay();

        // Ringkasan metrik (1 agregasi gabungan)
        $totalActiveBranches = Branch::where('is_active', true)->count();
        $trxSummary = Transaction::where('status', StatusTransaksi::Paid)
            ->selectRaw('COALESCE(SUM(total), 0) as total_omzet, COUNT(*) as total_count')
            ->first();
        $totalOmzet = (float) ($trxSummary?->total_omzet ?? 0);
        $totalTransactions = (int) ($trxSummary?->total_count ?? 0);
        $totalBookings = Booking::count();

        // Tren 14 hari (dikelompokkan langsung di SQL)
        $trendStartDate = $today->copy()->subDays(13)->startOfDay();
        $trendMetrics = Transaction::query()
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$trendStartDate, $endOfDay])
            ->selectRaw('DATE(transaction_date) as date_key, COALESCE(SUM(total), 0) as day_omzet, COUNT(*) as day_count')
            ->groupByRaw('DATE(transaction_date)')
            ->get()
            ->keyBy('date_key');

        $omzetTrend = $this->buildDailyTrend($trendMetrics, $trendStartDate, $today);

        // Performa per cabang
        $branches = Branch::orderBy('name')->get();
        $branchMetrics = Transaction::query()
            ->where('status', StatusTransaksi::Paid)
            ->selectRaw('branch_id, count(*) as total_count, sum(total) as sum_total')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $bookingCounts = Booking::query()
            ->selectRaw('branch_id, count(*) as total_bookings')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $branchPerformance = [];

        foreach ($branches as $branch) {
            $metric = $branchMetrics->get($branch->id);
            $bCount = $metric ? (int) $metric->total_count : 0;
            $bOmzet = $metric ? (float) $metric->sum_total : 0.0;
            $bAvg = $bCount > 0 ? round($bOmzet / $bCount, 2) : 0.0;
            $bBookings = (int) ($bookingCounts->get($branch->id)?->total_bookings ?? 0);

            $branchPerformance[] = [
                'id' => $branch->id,
                'name' => $branch->name,
                'display_name' => $branch->display_name,
                'address' => $branch->address,
                'is_active' => $branch->is_active,
                'total_transactions' => $bCount,
                'total_omzet' => $bOmzet,
                'total_bookings' => $bBookings,
                'average_transaction_value' => $bAvg,
            ];
        }

        usort($branchPerformance, fn ($a, $b) => $b['total_omzet'] <=> $a['total_omzet']);

        // Layanan terlaris
        $topServices = $this->getTopServices(5);

        // Performa karyawan
        $topEmployees = $this->getTopEmployees(5);

        // Efektivitas promo
        $promoEffectiveness = $this->getPromoEffectiveness();

        // Transaksi terbaru (Latest 7)
        $recentTransactions = Transaction::with(['branch', 'cashier', 'payments'])
            ->where('status', StatusTransaksi::Paid)
            ->latest('transaction_date')
            ->limit(7)
            ->get();

        return [
            'metrics' => [
                'total_active_branches' => $totalActiveBranches,
                'total_omzet' => $totalOmzet,
                'total_transactions' => $totalTransactions,
                'total_bookings' => $totalBookings,
            ],
            'omzet_trend' => $omzetTrend,
            'branch_performance' => $branchPerformance,
            'top_services' => $topServices,
            'top_employees' => $topEmployees,
            'promo_effectiveness' => $promoEffectiveness,
            'recent_transactions' => $recentTransactions,
        ];
    }

    /**
     * Data tren omzet harian.
     *
     * @param  Collection<string, mixed>  $trendMetrics
     * @return array{data: array<int, array<string, mixed>>, max_omzet: float}
     */
    protected function buildDailyTrend(Collection $trendMetrics, Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $data = [];
        $maxOmzet = 0.0;

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $metric = $trendMetrics->get($key);

            $dayOmzet = $metric ? (float) $metric->day_omzet : 0.0;
            $dayCount = $metric ? (int) $metric->day_count : 0;

            if ($dayOmzet > $maxOmzet) {
                $maxOmzet = $dayOmzet;
            }

            $data[] = [
                'date' => $key,
                'label' => $date->translatedFormat('d M'),
                'day_name' => $date->translatedFormat('D'),
                'omzet' => $dayOmzet,
                'transactions_count' => $dayCount,
            ];
        }

        $safeMax = max(1, $maxOmzet);
        foreach ($data as &$point) {
            $point['percentage'] = min(100, round(($point['omzet'] / $safeMax) * 100));
        }

        return [
            'data' => $data,
            'max_omzet' => $maxOmzet,
        ];
    }

    /**
     * Layanan terlaris.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getTopServices(int $limit = 5): array
    {
        $details = TransactionDetail::query()
            ->whereRelation('transaction', 'status', StatusTransaksi::Paid)
            ->with('service.category')
            ->selectRaw('service_id, sum(qty) as total_qty, sum(subtotal) as total_revenue')
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $result = [];

        foreach ($details as $item) {
            $service = $item->service;

            $result[] = [
                'service_id' => $item->service_id,
                'name' => $service?->name ?? 'Layanan',
                'category_name' => $service?->category?->name ?? 'Umum',
                'total_qty' => (float) $item->total_qty,
                'total_revenue' => (float) $item->total_revenue,
            ];
        }

        return $result;
    }

    /**
     * Karyawan dengan omzet tertinggi (agregasi terindeks).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getTopEmployees(int $limit = 5): array
    {
        $transactions = Transaction::query()
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('employees', 'bookings.employee_id', '=', 'employees.id')
            ->leftJoin('branches', 'employees.branch_id', '=', 'branches.id')
            ->where('transactions.status', StatusTransaksi::Paid)
            ->selectRaw('
                employees.id as employee_id,
                employees.name as name,
                branches.name as branch_name,
                COUNT(transactions.id) as transaction_count,
                COALESCE(SUM(transactions.total), 0) as total_revenue
            ')
            ->groupBy('employees.id', 'employees.name', 'branches.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $employeeStats = [];

        foreach ($transactions as $stat) {
            $employeeStats[$stat->employee_id] = [
                'employee_id' => $stat->employee_id,
                'name' => $stat->name,
                'branch_name' => Branch::cleanBusinessName($stat->branch_name) ?: 'Usaha',
                'transaction_count' => (int) $stat->transaction_count,
                'total_revenue' => (float) $stat->total_revenue,
            ];
        }

        if (count($employeeStats) < $limit) {
            $activeEmps = Employee::with('branch')->where('status', 'active')->limit($limit)->get();
            foreach ($activeEmps as $emp) {
                if (! isset($employeeStats[$emp->id])) {
                    $employeeStats[$emp->id] = [
                        'employee_id' => $emp->id,
                        'name' => $emp->name,
                        'branch_name' => $emp->branch?->display_name ?? 'Usaha',
                        'transaction_count' => 0,
                        'total_revenue' => 0.0,
                    ];
                }
            }
        }

        $result = array_values($employeeStats);
        usort($result, fn ($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        return array_slice($result, 0, $limit);
    }

    /**
     * Efektivitas penggunaan promo (agregasi SQL langsung).
     *
     * @return array{total_usages: int, total_discount_given: float, top_promotions: array<int, array<string, mixed>>}
     */
    protected function getPromoEffectiveness(): array
    {
        $totalUsages = PromotionUsage::count();
        $totalDiscountGiven = (float) Transaction::where('status', StatusTransaksi::Paid)->sum('discount');

        $topPromotions = PromotionUsage::query()
            ->join('promotions', 'promotion_usages.promotion_id', '=', 'promotions.id')
            ->leftJoin('transactions', 'promotion_usages.transaction_id', '=', 'transactions.id')
            ->selectRaw('
                promotion_usages.promotion_id,
                promotions.code,
                promotions.name,
                COUNT(promotion_usages.id) as usage_count,
                COALESCE(SUM(transactions.discount), 0) as total_discount
            ')
            ->groupBy('promotion_usages.promotion_id', 'promotions.code', 'promotions.name')
            ->orderByDesc('usage_count')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'promotion_id' => $p->promotion_id,
                'code' => $p->code ?? 'VOUCHER',
                'name' => $p->name ?? 'Promo Diskon',
                'usage_count' => (int) $p->usage_count,
                'total_discount' => (float) $p->total_discount,
            ])
            ->toArray();

        return [
            'total_usages' => $totalUsages,
            'total_discount_given' => $totalDiscountGiven,
            'top_promotions' => $topPromotions,
        ];
    }
}
