<?php

namespace App\Services\LaporanKeuangan\Queries;

use App\Models\EmployeeCommission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class KaryawanLaporanQuery
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
     * Base commission builder query.
     */
    protected function baseCommissionQuery(): Builder
    {
        $query = EmployeeCommission::query();

        if ($this->branchId) {
            $query->whereHas('employee', function (Builder $q) {
                $q->where('branch_id', $this->branchId);
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate) {
            $query->where('created_at', '>=', $this->startDate);
        } elseif ($this->endDate) {
            $query->where('created_at', '<=', $this->endDate);
        }

        return $query;
    }

    /**
     * Ringkasan komisi karyawan.
     */
    public function getEmployeeCommissionSummary(): array
    {
        return $this->baseCommissionQuery()
            ->selectRaw('employee_id, SUM(commission_amount) as total_commission, COUNT(*) as total_services')
            ->groupBy('employee_id')
            ->get()
            ->toArray();
    }
}
