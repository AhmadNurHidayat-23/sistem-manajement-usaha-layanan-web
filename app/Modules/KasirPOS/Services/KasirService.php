<?php

namespace App\Modules\KasirPOS\Services;

use App\Enums\CashierShiftStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class KasirService
{
    /**
     * Cabang aktif yang dapat diakses.
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

        if ($roleName === 'cashier') {
            $branches = $user->branches()->where('is_active', true)->get();
            if ($branches->isEmpty() && $user->employee?->branch) {
                return collect([$user->employee->branch]);
            }

            return $branches;
        }

        return collect();
    }

    /**
     * Cabang aktif saat ini.
     */
    public function getActiveBranchForUser(User $user, ?int $branchId = null): ?Branch
    {
        $branches = $this->getAccessibleBranches($user);

        if ($branchId) {
            return $branches->firstWhere('id', $branchId);
        }

        return $branches->first();
    }

    /**
     * Otorisasi akses cabang.
     *
     * @throws AuthorizationException
     */
    public function authorizeBranchAccess(User $user, Branch $branch): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        $accessibleBranches = $this->getAccessibleBranches($user);

        if ($accessibleBranches->contains('id', $branch->id)) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin untuk mengakses kasir pada cabang ini.');
    }

    /**
     * Shift kasir aktif saat ini.
     */
    public function getActiveShift(User $user, ?int $branchId = null): ?CashierShift
    {
        $query = CashierShift::with(['branch', 'user'])
            ->where('user_id', $user->id)
            ->where('status', CashierShiftStatus::Open);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->latest('opened_at')->first();
    }

    /**
     * Open a new cashier shift.
     *
     * @throws ValidationException
     */
    public function openShift(User $user, int $branchId, float $openingCash): CashierShift
    {
        $branch = Branch::findOrFail($branchId);
        $this->authorizeBranchAccess($user, $branch);

        $existingShift = $this->getActiveShift($user, $branchId);
        if ($existingShift) {
            throw ValidationException::withMessages([
                'shift' => 'Anda masih memiliki shift yang sedang aktif pada cabang ini.',
            ]);
        }

        return CashierShift::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'opened_at' => Carbon::now(),
            'opening_cash' => $openingCash,
            'status' => CashierShiftStatus::Open,
        ]);
    }

    /**
     * Close an active cashier shift.
     *
     * @throws ValidationException
     */
    public function closeShift(CashierShift $shift, float $closingCash, ?string $notes = null): CashierShift
    {
        if ($shift->status !== CashierShiftStatus::Open) {
            throw ValidationException::withMessages([
                'shift' => 'Shift kasir ini sudah ditutup sebelumnya.',
            ]);
        }

        $summary = $this->getShiftSummary($shift);
        $expectedCash = $summary['expected_cash'];
        $cashDifference = $closingCash - $expectedCash;

        $shift->update([
            'closed_at' => Carbon::now(),
            'closing_cash' => $closingCash,
            'cash_difference' => $cashDifference,
            'status' => CashierShiftStatus::Closed,
        ]);

        return $shift->fresh();
    }

    /**
     * Audit kas dan statistik shift.
     *
     * @return array{
     *     opening_cash: float,
     *     total_transactions: int,
     *     total_sales: float,
     *     cash_sales: float,
     *     non_cash_sales: float,
     *     expected_cash: float,
     *     closing_cash: ?float,
     *     cash_difference: ?float
     * }
     */
    public function getShiftSummary(CashierShift $shift): array
    {
        $transactionsQuery = Transaction::where('cashier_id', $shift->user_id)
            ->where('branch_id', $shift->branch_id)
            ->where('status', StatusTransaksi::Paid)
            ->where('transaction_date', '>=', $shift->opened_at);

        if ($shift->closed_at) {
            $transactionsQuery->where('transaction_date', '<=', $shift->closed_at);
        }

        $transactionIds = (clone $transactionsQuery)->pluck('id');

        $totalTransactions = $transactionIds->count();
        $totalSales = (float) (clone $transactionsQuery)->sum('total');

        $cashSales = (float) Payment::whereIn('transaction_id', $transactionIds)
            ->where('payment_method', 'cash')
            ->whereIn('status', [StatusPembayaran::Completed, StatusPembayaran::Paid])
            ->sum('amount');

        $nonCashSales = (float) Payment::whereIn('transaction_id', $transactionIds)
            ->where('payment_method', '!=', 'cash')
            ->whereIn('status', [StatusPembayaran::Completed, StatusPembayaran::Paid])
            ->sum('amount');

        $expectedCash = (float) $shift->opening_cash + $cashSales;

        return [
            'opening_cash' => (float) $shift->opening_cash,
            'total_transactions' => $totalTransactions,
            'total_sales' => $totalSales,
            'cash_sales' => $cashSales,
            'non_cash_sales' => $nonCashSales,
            'expected_cash' => $expectedCash,
            'closing_cash' => $shift->closing_cash ? (float) $shift->closing_cash : null,
            'cash_difference' => $shift->cash_difference !== null ? (float) $shift->cash_difference : null,
        ];
    }
}
