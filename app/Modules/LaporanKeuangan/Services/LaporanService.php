<?php

namespace App\Modules\LaporanKeuangan\Services;

use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanService
{
    public function __construct(
        protected TrenLaporanService $trendService,
        protected LaporanBreakdownService $breakdownService,
        protected LaporanKinerjaService $performanceService,
        protected EksporLaporanService $exportService,
        protected LaporanPeriodResolver $periodResolver = new LaporanPeriodResolver,
    ) {}

    /**
     * Resolusi rentang tanggal laporan.
     *
     * @return array{start_date: Carbon, end_date: Carbon, period: string}
     */
    public function resolveDateRange(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->periodResolver->resolve($period, $startDate, $endDate);
    }

    /**
     * Ambil cabang yang dapat diakses pengguna.
     *
     * @return Collection<int, Branch>
     */
    public function getAccessibleBranches(User $user): Collection
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }

        if ($roleName === 'manager') {
            return $user->branches()->where('is_active', true)->orderBy('name')->get();
        }

        return collect();
    }

    /**
     * Otorisasi akses laporan cabang.
     *
     * @throws AuthorizationException
     */
    public function authorizeReportAccess(User $user, ?int $branchId): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager') {
            $managedIds = $user->branches()->pluck('branches.id')->toArray();
            if ($branchId !== null && ! in_array($branchId, $managedIds, true)) {
                throw new AuthorizationException('Manager hanya dapat mengakses laporan untuk cabang yang dikelola.');
            }

            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin mengakses modul laporan.');
    }

    /**
     * Generate data laporan komprehensif.
     *
     * @param  array{period?: ?string, branch_id?: ?int, start_date?: ?string, end_date?: ?string}  $filters
     * @return array<string, mixed>
     *
     * @throws AuthorizationException
     */
    public function getComprehensiveReport(User $user, array $filters = []): array
    {
        $roleName = $user->role?->name;
        $accessibleBranches = $this->getAccessibleBranches($user);

        // Resolusi cabang
        $targetBranchId = ! empty($filters['branch_id']) ? (int) $filters['branch_id'] : null;

        if ($roleName === 'manager' && $targetBranchId === null) {
            $targetBranchId = $accessibleBranches->first()?->id;
        }

        $this->authorizeReportAccess($user, $targetBranchId);

        // Resolusi tanggal
        $dates = $this->resolveDateRange(
            $filters['period'] ?? null,
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];
        $period = $dates['period'];

        $selectedBranch = $targetBranchId ? Branch::find($targetBranchId) : null;
        $isCombined = $targetBranchId === null && $roleName === 'owner';

        // Query transaksi paid
        $transactionsQuery = Transaction::query()
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($targetBranchId) {
            $transactionsQuery->where('branch_id', $targetBranchId);
        }

        $transactions = $transactionsQuery->with([
            'branch',
            'cashier',
            'details.service.category',
            'payments',
            'booking.user',
            'order.user',
            'booking.employee',
        ])->get();

        // Ringkasan metrik
        $totalTransactions = $transactions->count();
        $totalOmzet = (float) $transactions->sum('total');
        $grossOmzet = (float) $transactions->sum('subtotal');
        $totalDiscount = (float) $transactions->sum('discount');
        $averageTransaction = $totalTransactions > 0 ? round($totalOmzet / $totalTransactions, 2) : 0;

        // Delegasi tren, rincian, dan performa
        $trend = $this->trendService->buildTrendData($transactions, $startDate, $endDate);
        $paymentMethods = $this->breakdownService->buildPaymentMethodsBreakdown($transactions, $totalOmzet);
        $promotionsData = $this->breakdownService->buildPromotionsBreakdown($startDate, $endDate, $targetBranchId);
        $servicesBreakdown = $this->breakdownService->buildServicesBreakdown($transactions, $totalOmzet);
        $employeesBreakdown = $this->performanceService->buildEmployeesBreakdown($targetBranchId, $transactions);
        $commissionsData = $this->performanceService->buildCommissionsBreakdown($startDate, $endDate, $targetBranchId);

        $branchPerformance = [];
        if ($isCombined) {
            $branchPerformance = $this->performanceService->buildBranchPerformance($startDate, $endDate);
        }

        return [
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'formatted_start_date' => $startDate->translatedFormat('d M Y'),
            'formatted_end_date' => $endDate->translatedFormat('d M Y'),
            'branch_id' => $targetBranchId,
            'selected_branch' => $selectedBranch,
            'is_combined' => $isCombined,
            'branches' => $accessibleBranches,
            'summary' => [
                'total_omzet' => $totalOmzet,
                'gross_omzet' => $grossOmzet,
                'total_discount' => $totalDiscount,
                'total_transactions' => $totalTransactions,
                'average_transaction_value' => $averageTransaction,
            ],
            'trend' => $trend,
            'payment_methods' => $paymentMethods,
            'promotions' => $promotionsData,
            'services' => $servicesBreakdown,
            'employees' => $employeesBreakdown,
            'commissions' => $commissionsData,
            'branch_performance' => $branchPerformance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Laporan ringkasan sederhana.
     *
     * @return array<string, mixed>
     */
    public function getSummaryReport(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $dates = $this->resolveDateRange('custom', $startDate, $endDate);
        $sDate = $dates['start_date'];
        $eDate = $dates['end_date'];

        $query = Transaction::query()
            ->where('status', StatusTransaksi::Paid)
            ->whereBetween('transaction_date', [$sDate, $eDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $transactions = $query->with('details.service')->get();
        $totalOmzet = (float) $transactions->sum('total');

        return [
            'branch_id' => $branchId,
            'is_combined' => is_null($branchId),
            'start_date' => $sDate->format('Y-m-d'),
            'end_date' => $eDate->format('Y-m-d'),
            'total_omzet' => $totalOmzet,
            'total_transactions' => $transactions->count(),
            'payment_methods' => $this->breakdownService->buildPaymentMethodsBreakdown($transactions, $totalOmzet),
            'promo_usages' => $this->breakdownService->buildPromotionsBreakdown($sDate, $eDate, $branchId)['list'],
            'revenue_per_service' => $this->breakdownService->buildServicesBreakdown($transactions, $totalOmzet),
            'employee_commissions' => $this->performanceService->buildCommissionsBreakdown($sDate, $eDate, $branchId)['by_employee'],
        ];
    }

    /**
     * Ekspor laporan ke CSV.
     *
     * @param  array{period?: ?string, branch_id?: ?int, start_date?: ?string, end_date?: ?string}  $filters
     *
     * @throws AuthorizationException
     */
    public function exportCsv(User $user, array $filters = []): StreamedResponse
    {
        $report = $this->getComprehensiveReport($user, $filters);

        return $this->exportService->exportCsv($report);
    }

    /**
     * Ekspor laporan ke XLSX asli.
     *
     * @param  array{period?: ?string, branch_id?: ?int, start_date?: ?string, end_date?: ?string}  $filters
     *
     * @throws AuthorizationException
     */
    public function exportExcel(User $user, array $filters = []): StreamedResponse
    {
        $report = $this->getComprehensiveReport($user, $filters);

        return $this->exportService->exportExcel($report);
    }

    /**
     * Ekspor laporan ke PDF asli server-side.
     *
     * @param  array{period?: ?string, branch_id?: ?int, start_date?: ?string, end_date?: ?string}  $filters
     *
     * @throws AuthorizationException
     */
    public function exportPdf(User $user, array $filters = []): Response
    {
        $report = $this->getComprehensiveReport($user, $filters);

        return $this->exportService->exportPdf($report);
    }
}
