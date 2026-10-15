<?php

namespace App\Services\Karyawan;

use App\Enums\CommissionStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class KaryawanService
{
    public function __construct(
        protected KinerjaKaryawanService $performanceService
    ) {}

    /**
     * Daftar terpaginasi employees.
     *
     * @param  array{branch_id?: ?int, status?: ?string, skill?: ?string, q?: ?string}  $filters
     */
    public function getEmployees(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with([
            'user',
            'branch',
            'skills',
            'shifts' => fn ($q) => $q->whereDate('shift_date', Carbon::today()),
        ])
            ->withCount(['shifts', 'commissions', 'bookings'])
            ->withSum(['commissions as pending_commissions_sum' => fn ($q) => $q->where('status', CommissionStatus::Pending->value)], 'commission_amount')
            ->withSum(['commissions as approved_commissions_sum' => fn ($q) => $q->where('status', CommissionStatus::Approved->value)], 'commission_amount');

        $roleName = $user->role?->name;

        if ($roleName === 'manager') {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $managedBranchIds);
        } elseif ($roleName === 'employee') {
            $employeeId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $query->where('id', $employeeId ?: 0);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['skill'])) {
            $skillSearch = '%'.$filters['skill'].'%';
            $query->whereHas('skills', function ($q) use ($skillSearch) {
                $q->where('skill_name', 'like', $skillSearch);
            });
        }

        if (! empty($filters['q'])) {
            $searchTerm = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $searchTerm)->orWhere('email', 'like', $searchTerm));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Otorisasi akses detail karyawan.
     *
     * @throws AuthorizationException
     */
    public function authorizeEmployeeAccess(User $user, Employee $employee): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $employee->branch_id)->exists()) {
            return;
        }

        if ($roleName === 'employee' && ($employee->user_id === $user->id || $user->employee?->id === $employee->id)) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin untuk mengakses data karyawan ini.');
    }

    /**
     * Otorisasi kelola data karyawan.
     *
     * @throws AuthorizationException
     */
    public function authorizeEmployeeManagement(User $user, Employee $employee): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $employee->branch_id)->exists()) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin untuk mengubah data karyawan ini.');
    }

    /**
     * Tambah karyawan baru.
     *
     * @param  array{name: string, phone: string, branch_id: int, user_id?: ?int, status?: ?string, skills?: mixed}  $data
     *
     * @throws AuthorizationException
     */
    public function createEmployee(User $user, array $data): Employee
    {
        $roleName = $user->role?->name;
        if ($roleName === 'manager' && ! $user->branches()->where('branches.id', $data['branch_id'])->exists()) {
            throw new AuthorizationException('Manager hanya dapat menambahkan karyawan ke cabang yang dikelola.');
        }

        $employee = Employee::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'branch_id' => $data['branch_id'],
            'user_id' => $data['user_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        if (! empty($data['skills'])) {
            $this->syncSkills($employee, $data['skills']);
        }

        return $employee->load(['branch', 'user', 'skills']);
    }

    /**
     * Perbarui karyawan.
     *
     * @param  array{name?: string, phone?: string, branch_id?: int, user_id?: ?int, status?: string, skills?: mixed}  $data
     *
     * @throws AuthorizationException
     */
    public function updateEmployee(User $user, Employee $employee, array $data): Employee
    {
        $this->authorizeEmployeeManagement($user, $employee);

        if (isset($data['branch_id']) && $user->role?->name === 'manager' && ! $user->branches()->where('branches.id', $data['branch_id'])->exists()) {
            throw new AuthorizationException('Manager tidak dapat memindahkan karyawan ke cabang di luar pengelolaannya.');
        }

        $employee->update([
            'name' => $data['name'] ?? $employee->name,
            'phone' => $data['phone'] ?? $employee->phone,
            'branch_id' => $data['branch_id'] ?? $employee->branch_id,
            'user_id' => array_key_exists('user_id', $data) ? $data['user_id'] : $employee->user_id,
            'status' => $data['status'] ?? $employee->status,
        ]);

        if (array_key_exists('skills', $data)) {
            $this->syncSkills($employee, $data['skills'] ?? []);
        }

        return $employee->fresh(['branch', 'user', 'skills']);
    }

    /**
     * Ubah status karyawan.
     *
     * @throws AuthorizationException
     */
    public function toggleEmployeeStatus(User $user, Employee $employee): Employee
    {
        $this->authorizeEmployeeManagement($user, $employee);

        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);

        return $employee;
    }

    /**
     * Sinkronisasi keahlian.
     */
    public function syncSkills(Employee $employee, mixed $skills): void
    {
        $skillList = [];

        if (is_string($skills)) {
            $skillList = array_map('trim', explode(',', $skills));
        } elseif (is_array($skills)) {
            foreach ($skills as $item) {
                if (is_string($item)) {
                    foreach (explode(',', $item) as $s) {
                        $trimmed = trim($s);
                        if ($trimmed !== '') {
                            $skillList[] = $trimmed;
                        }
                    }
                }
            }
        }

        $skillList = array_values(array_unique(array_filter($skillList)));

        // Hapus skill lama
        $employee->skills()->delete();

        // Buat skill baru
        foreach ($skillList as $skillName) {
            $employee->skills()->create([
                'skill_name' => $skillName,
            ]);
        }
    }

    /**
     * Ringkasan performa karyawan.
     *
     * @return array<string, mixed>
     */
    public function getEmployeePerformanceSummary(Employee $employee): array
    {
        return $this->performanceService->getEmployeePerformanceSummary($employee);
    }

    /**
     * Cabang yang dapat diakses.
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

        if ($roleName === 'employee') {
            $branch = $user->employee?->branch;

            return $branch ? new Collection([$branch]) : new Collection;
        }

        return new Collection;
    }

    /**
     * Akun user yang tersedia.
     */
    public function getAvailableUsersForEmployee(User $user, ?Employee $currentEmployee = null): Collection
    {
        $employeeRole = Role::where('name', 'employee')->first();
        if (! $employeeRole) {
            return new Collection;
        }

        $query = User::where('role_id', $employeeRole->id);

        if ($currentEmployee && $currentEmployee->user_id) {
            $currentUserId = $currentEmployee->user_id;
            $query->where(function ($q) use ($currentUserId) {
                $q->doesntHave('employee')
                    ->orWhere('id', $currentUserId);
            });
        } else {
            $query->doesntHave('employee');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Karyawan aktif per cabang.
     */
    public function getActiveEmployeesByBranch(int $branchId): Collection
    {
        return Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->with('skills')
            ->orderBy('name')
            ->get();
    }

    /**
     * Ringkasan dashboard karyawan.
     *
     * @return array{employee: ?Employee, performance: array<string, mixed>, todayShift: ?EmployeeShift, upcomingShifts: Collection}
     */
    public function getDashboardSummaryForEmployee(User $user): array
    {
        return $this->performanceService->getDashboardSummaryForEmployee($user);
    }
}
