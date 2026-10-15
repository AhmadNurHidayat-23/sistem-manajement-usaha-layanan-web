<?php

namespace App\Services\LaporanKeuangan\Queries;

use App\Enums\StatusTransaksi;
use App\Models\Payment;
use App\Models\PromotionUsage;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TransaksiLaporanQuery
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
     * Get total count of completed paid transactions.
     */
    public function getTransactionCount(): int
    {
        return $this->baseQuery()
            ->where('status', StatusTransaksi::Paid)
            ->count();
    }

    /**
     * Ringkasan per metode pembayaran.
     */
    public function getPaymentMethodBreakdown(): array
    {
        $transactionIds = $this->baseQuery()
            ->where('status', StatusTransaksi::Paid)
            ->pluck('id');

        return Payment::query()
            ->whereIn('transaction_id', $transactionIds)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->toArray();
    }

    /**
     * Ringkasan penggunaan promo.
     */
    public function getPromotionUsageSummary(): array
    {
        $query = PromotionUsage::query();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('used_at', [$this->startDate, $this->endDate]);
        }

        return $query->selectRaw('promotion_id, COUNT(*) as usage_count')
            ->groupBy('promotion_id')
            ->get()
            ->toArray();
    }
}
