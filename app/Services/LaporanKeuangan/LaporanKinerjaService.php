<?php

namespace App\Services\LaporanKeuangan;

use App\Enums\CommissionStatus;
use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanKinerjaService
{
    /**
     * Hitung rincian kontribusi pendapatan per karyawan.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return array<int, array<string, mixed>>
     */
    public function buildEmployeesBreakdown(?int $branchId, Collection $transactions): array
    {
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

        // Ambil data karyawan aktif
        $empQuery = Employee::with('branch')->where('status', 'active');
        if ($branchId) {
            $empQuery->where('branch_id', $branchId);
        }
        $allEmployees = $empQuery->get();

        foreach ($allEmployees as $emp) {
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

        $result = array_values($employeeStats);
        usort($result, fn ($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        return $result;
    }

    /**
     * Hitung rincian komisi karyawan.
     *
     * @param  Collection<int, Transaction>|null  $transactions
     * @return array{
     *     total_commission: float,
     *     paid_commission: float,
     *     pending_commission: float,
     *     by_employee: array<int, array<string, mixed>>
     * }
     */
    public function buildCommissionsBreakdown(?Carbon $startDate = null, ?Carbon $endDate = null, ?int $branchId = null, ?Collection $transactions = null): array
    {
        $query = EmployeeCommission::with(['employee.branch', 'service']);

        if ($transactions !== null) {
            $transactionIds = $transactions->pluck('id')->all();
            if (empty($transactionIds)) {
                return [
                    'total_commission' => 0.0,
                    'paid_commission' => 0.0,
                    'pending_commission' => 0.0,
                    'by_employee' => [],
                ];
            }
            $query->whereIn('transaction_id', $transactionIds);
        } else {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($branchId) {
                $query->whereHas('employee', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }
        }

        $commissions = $query->get();

        $totalCommission = (float) $commissions->sum('commission_amount');
        $paidCommission = (float) $commissions->where('status', CommissionStatus::Paid)->sum('commission_amount');
        $pendingCommission = (float) $commissions->where('status', CommissionStatus::Pending)->sum('commission_amount');

        $grouped = $commissions->groupBy('employee_id');
        $byEmployee = [];

        foreach ($grouped as $empId => $empCommissions) {
            $emp = $empCommissions->first()?->employee;
            $empTotal = (float) $empCommissions->sum('commission_amount');
            $empPaid = (float) $empCommissions->where('status', CommissionStatus::Paid)->sum('commission_amount');
            $empPending = (float) $empCommissions->where('status', CommissionStatus::Pending)->sum('commission_amount');

            $byEmployee[] = [
                'employee_id' => $empId,
                'employee_name' => $emp?->name ?? 'Karyawan',
                'branch_name' => $emp?->branch?->display_name ?? 'Usaha',
                'total_services' => $empCommissions->count(),
                'total_commission' => $empTotal,
                'paid_commission' => $empPaid,
                'pending_commission' => $empPending,
            ];
        }

        usort($byEmployee, fn ($a, $b) => $b['total_commission'] <=> $a['total_commission']);

        return [
            'total_commission' => $totalCommission,
            'paid_commission' => $paidCommission,
            'pending_commission' => $pendingCommission,
            'by_employee' => $byEmployee,
        ];
    }

    /**
     * Hitung perbandingan kinerja antar cabang (view Owner konsolidasi).
     *
     * @param  Collection<int, Transaction>|null  $transactions
     * @return array<int, array<string, mixed>>
     */
    public function buildBranchPerformance(?Carbon $startDate = null, ?Carbon $endDate = null, ?Collection $transactions = null): array
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($transactions !== null) {
            $trxStats = $transactions->groupBy('branch_id');

            $result = [];
            foreach ($branches as $branch) {
                $branchTrxs = $trxStats->get($branch->id, collect());
                $count = $branchTrxs->count();
                $omzet = (float) $branchTrxs->sum('total');
                $discount = (float) $branchTrxs->sum('discount');
                $avg = $count > 0 ? round($omzet / $count, 2) : 0.0;

                $result[] = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->display_name,
                    'address' => $branch->address,
                    'total_transactions' => $count,
                    'total_omzet' => $omzet,
                    'total_discount' => $discount,
                    'average_transaction_value' => $avg,
                ];
            }

            usort($result, fn ($a, $b) => $b['total_omzet'] <=> $a['total_omzet']);

            return $result;
        }

        $query = Transaction::query()
            ->where('status', StatusTransaksi::Paid);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $trxStats = $query
            ->selectRaw('branch_id, count(*) as total_count, sum(total) as sum_total, sum(discount) as sum_discount')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $result = [];

        foreach ($branches as $branch) {
            $stat = $trxStats->get($branch->id);
            $count = $stat ? (int) $stat->total_count : 0;
            $omzet = $stat ? (float) $stat->sum_total : 0.0;
            $discount = $stat ? (float) $stat->sum_discount : 0.0;
            $avg = $count > 0 ? round($omzet / $count, 2) : 0.0;

            $result[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->display_name,
                'address' => $branch->address,
                'total_transactions' => $count,
                'total_omzet' => $omzet,
                'total_discount' => $discount,
                'average_transaction_value' => $avg,
            ];
        }

        usort($result, fn ($a, $b) => $b['total_omzet'] <=> $a['total_omzet']);

        return $result;
    }
}
