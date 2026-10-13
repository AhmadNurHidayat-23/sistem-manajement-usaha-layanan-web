<?php

namespace App\Modules\LaporanKeuangan\Services\Queries;

use App\Enums\StatusTransaksi;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PendapatanLaporanQuery
{
    protected ?int $branchId = null;

    protected ?Carbon $startDate = null;

    protected ?Carbon $endDate = null;

    /**
     * Filter berdasarkan cabang.
     */
    public function forBranch(?int $branchId): self
    {
        $this->branchId = $branchId;

        return $this;
    }

    /**
     * Filter query by date range.
     */
    public function between(?string $startDate, ?string $endDate): self
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        return $this;
    }

    /**
     * Base transaction builder query.
     */
    protected function baseQuery(): Builder
    {
        $query = Transaction::query();

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate) {
            $query->where('transaction_date', '>=', $this->startDate);
        } elseif ($this->endDate) {
            $query->where('transaction_date', '<=', $this->endDate);
        }

        return $query;
    }

    /**
     * Calculate total omzet (revenue).
     */
    public function getTotalOmzet(): float
    {
        return (float) $this->baseQuery()
            ->where('status', StatusTransaksi::Paid)
            ->sum('total');
    }

    /**
     * Get revenue breakdown per service.
     */
    public function getRevenuePerService(): array
    {
        $transactionIds = $this->baseQuery()
            ->where('status', StatusTransaksi::Paid)
            ->pluck('id');

        return TransactionDetail::query()
            ->whereIn('transaction_id', $transactionIds)
            ->selectRaw('service_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('service_id')
            ->get()
            ->toArray();
    }
}
