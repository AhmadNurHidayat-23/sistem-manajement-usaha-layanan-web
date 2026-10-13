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

        // Ringkasan metrik
        $totalActiveBranches = Branch::where('is_active', true)->count();
        $totalOmzet = (float) Transaction::where('status', StatusTransaksi::Paid)->sum('total');
        $totalTransactions = Transaction::where('status', StatusTransaksi::Paid)->count();
        $totalBookings = Booking::count();

        // Tren 14 hari
        $trendStartDate = $today->copy()->subDays(13)->startOfDay();
        $trendTransactions = Transaction::query()
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$trendStartDate, $endOfDay])
            ->select(['id', 'total', 'transaction_date'])
            ->get();

        $omzetTrend = $this->buildDailyTrend($trendTransactions, $trendStartDate, $today);

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
     * @param  Collection<int, Transaction>  $transactions
     * @return array{data: array<int, array<string, mixed>>, max_omzet: float}
     */
    protected function buildDailyTrend(Collection $transactions, Carbon $startDate, Carbon $endDate): array
    {
        $grouped = $transactions->groupBy(function ($trx) {
            return Carbon::parse($trx->transaction_date)->format('Y-m-d');
        });

        $period = CarbonPeriod::create($startDate, $endDate);
        $data = [];
        $maxOmzet = 0.0;

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $dayTrxs = $grouped->get($key, collect());

            $dayOmzet = (float) $dayTrxs->sum('total');
            $dayCount = $dayTrxs->count();

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
     * Karyawan dengan omzet tertinggi.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getTopEmployees(int $limit = 5): array
    {
        $transactions = Transaction::with(['booking.employee.branch'])
            ->where('status', StatusTransaksi::Paid)
            ->whereNotNull('booking_id')
            ->get();

        $employeeStats = [];

        foreach ($transactions as $trx) {
            $emp = $trx->booking?->employee;
            if (! $emp) {
                continue;
            }

            $empId = $emp->id;
            if (! isset($employeeStats[$empId])) {
                $employeeStats[$empId] = [
                    'employee_id' => $empId,
                    'name' => $emp->name,
                    'branch_name' => $emp->branch?->display_name ?? 'Usaha',
                    'transaction_count' => 0,
                    'total_revenue' => 0.0,
                ];
            }

            $employeeStats[$empId]['transaction_count']++;
            $employeeStats[$empId]['total_revenue'] += (float) $trx->total;
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
     * Efektivitas penggunaan promo.
     *
     * @return array{total_usages: int, total_discount_given: float, top_promotions: array<int, array<string, mixed>>}
     */
    protected function getPromoEffectiveness(): array
    {
        $totalUsages = PromotionUsage::count();
        $totalDiscountGiven = (float) Transaction::where('status', StatusTransaksi::Paid)->sum('discount');

        $usages = PromotionUsage::with(['promotion', 'transaction'])->get();
        $grouped = $usages->groupBy('promotion_id');
        $topPromos = [];

        foreach ($grouped as $promoId => $promoUsages) {
            $promo = $promoUsages->first()?->promotion;
            $count = $promoUsages->count();
            $discount = (float) $promoUsages->sum(fn ($u) => (float) ($u->transaction?->discount ?? 0));

            $topPromos[] = [
                'promotion_id' => $promoId,
                'code' => $promo?->code ?? 'VOUCHER',
                'name' => $promo?->name ?? 'Promo Diskon',
                'usage_count' => $count,
                'total_discount' => $discount,
            ];
        }

        usort($topPromos, fn ($a, $b) => $b['usage_count'] <=> $a['usage_count']);

        return [
            'total_usages' => $totalUsages,
            'total_discount_given' => $totalDiscountGiven,
            'top_promotions' => array_slice($topPromos, 0, 5),
        ];
    }
}
