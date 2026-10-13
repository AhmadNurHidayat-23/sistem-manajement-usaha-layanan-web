<?php

namespace App\Modules\Karyawan\Services;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KomisiKaryawanService
{
    /**
     * Daftar terpaginasi commissions.
     *
     * @param  array{employee_id?: ?int, branch_id?: ?int, status?: ?string, start_date?: ?string, end_date?: ?string}  $filters
     */
    public function getCommissions(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = EmployeeCommission::with(['employee.branch', 'service', 'transaction']);

        $roleName = $user->role?->name;

        if ($roleName === 'manager') {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereHas('employee', function ($q) use ($managedBranchIds) {
                $q->whereIn('branch_id', $managedBranchIds);
            });
        } elseif ($roleName === 'employee') {
            $employeeId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $filters['employee_id'] = $employeeId ?: 0;
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }

        if (! empty($filters['branch_id'])) {
            $branchId = (int) $filters['branch_id'];
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Otorisasi kelola komisi karyawan.
     *
     * @throws AuthorizationException
     */
    public function authorizeCommissionManagement(User $user, Employee $employee): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $employee->branch_id)->exists()) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin mengelola komisi karyawan ini.');
    }

    /**
     * Catat data komisi.
     *
     * @param  array{employee_id: int, service_id: int, transaction_id?: ?int, commission_type: string, commission_value: float, commission_amount?: ?float, status?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function createCommission(User $user, array $data): EmployeeCommission
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $this->authorizeCommissionManagement($user, $employee);

        $service = Service::findOrFail($data['service_id']);

        $type = $data['commission_type'];
        $val = (float) $data['commission_value'];

        if (isset($data['commission_amount']) && $data['commission_amount'] > 0) {
            $amount = (float) $data['commission_amount'];
        } else {
            if ($type === CommissionType::Percentage->value) {
                $amount = round(((float) $service->price) * ($val / 100), 2);
            } else {
                $amount = $val;
            }
        }

        return EmployeeCommission::create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'transaction_id' => $data['transaction_id'] ?? null,
            'commission_type' => $type,
            'commission_value' => $val,
            'commission_amount' => $amount,
            'status' => $data['status'] ?? CommissionStatus::Pending->value,
        ]);
    }

    /**
     * Perbarui status komisi.
     *
     * @throws AuthorizationException
     */
    public function updateCommissionStatus(User $user, EmployeeCommission $commission, CommissionStatus $status): EmployeeCommission
    {
        $employee = $commission->employee;
        if ($employee) {
            $this->authorizeCommissionManagement($user, $employee);
        }

        $commission->update(['status' => $status]);

        return $commission->fresh(['employee', 'service', 'transaction']);
    }

    /**
     * Ringkasan statistik komisi.
     *
     * @return array{pending: float, approved: float, paid: float, total: float, count: int}
     */
    public function getCommissionOverview(User $user, ?int $branchId = null, ?int $employeeId = null): array
    {
        $query = EmployeeCommission::query();

        $roleName = $user->role?->name;

        if ($roleName === 'manager') {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereHas('employee', function ($q) use ($managedBranchIds) {
                $q->whereIn('branch_id', $managedBranchIds);
            });
        } elseif ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $employeeId = $empId ?: 0;
            $branchId = null;
            $query->where('employee_id', $employeeId);
        }

        if ($branchId) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $branchId));
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $pending = (float) (clone $query)->where('status', CommissionStatus::Pending->value)->sum('commission_amount');
        $approved = (float) (clone $query)->where('status', CommissionStatus::Approved->value)->sum('commission_amount');
        $paid = (float) (clone $query)->where('status', CommissionStatus::Paid->value)->sum('commission_amount');
        $total = $pending + $approved + $paid;
        $count = (clone $query)->count();

        return [
            'pending' => $pending,
            'approved' => $approved,
            'paid' => $paid,
            'total' => $total,
            'count' => $count,
        ];
    }

    /**
     * Hitung dan catat estimasi komisi otomatis dari transaksi yang telah diselesaikan (KRY-F-004).
     *
     * @return Collection<int, EmployeeCommission>
     */
    public function processTransactionCommission(Transaction $transaction): Collection
    {
        return app(KomisiOtomatisService::class)->process($transaction);
    }
}
