<?php

namespace App\Modules\Karyawan\Services;

use App\Enums\StatusShiftKaryawan;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftKaryawanService
{
    /**
     * Daftar terpaginasi employee shifts.
     *
     * @param  array{date?: ?string, branch_id?: ?int, employee_id?: ?int, status?: ?string}  $filters
     */
    public function getShifts(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = EmployeeShift::with(['employee.skills', 'branch']);

        $roleName = $user->role?->name;

        if ($roleName === 'manager') {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $managedBranchIds);
        } elseif ($roleName === 'employee') {
            $employeeId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $query->where('employee_id', $employeeId ?: 0);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }

        $today = Carbon::today()->toDateString();
        $scope = $filters['scope'] ?? null;

        if ($scope === 'today') {
            $query->whereDate('shift_date', $today);
        } elseif ($scope === 'upcoming') {
            $query->whereDate('shift_date', '>', $today);
        } elseif ($scope === 'history') {
            $query->whereDate('shift_date', '<', $today);
        } elseif (! empty($filters['date'])) {
            $query->whereDate('shift_date', $filters['date']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($scope === 'upcoming') {
            $query->orderBy('shift_date', 'asc')->orderBy('start_time', 'asc');
        } elseif ($scope === 'history') {
            $query->orderBy('shift_date', 'desc')->orderBy('start_time', 'asc');
        } elseif ($scope === 'today') {
            $query->orderBy('start_time', 'asc');
        } else {
            $query->orderBy('shift_date', 'desc')->orderBy('start_time', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Otorisasi shift cabang.
     *
     * @throws AuthorizationException
     */
    public function authorizeShiftManagement(User $user, int $branchId): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $branchId)->exists()) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin mengelola jadwal shift pada cabang ini.');
    }

    /**
     * Tambah shift karyawan baru.
     *
     * @param  array{employee_id: int, branch_id: int, shift_date: string, start_time: string, end_time: string, status?: ?string}  $data
     *
     * @throws AuthorizationException|\InvalidArgumentException
     */
    public function createShift(User $user, array $data): EmployeeShift
    {
        $this->authorizeShiftManagement($user, (int) $data['branch_id']);

        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        if ($start->diffInMinutes($end, false) > 480) {
            throw new \InvalidArgumentException('Durasi satu shift kerja tidak boleh melebihi 8 jam.');
        }

        $employee = Employee::findOrFail($data['employee_id']);

        // Cegah bentrok jadwal shift untuk karyawan yang sama
        $clash = EmployeeShift::where('employee_id', $employee->id)
            ->whereDate('shift_date', $data['shift_date'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($clash) {
            throw new \InvalidArgumentException('Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');
        }

        $shiftDate = Carbon::parse($data['shift_date'])->startOfDay();
        $isFuture = $shiftDate->isFuture();
        $status = $isFuture ? StatusShiftKaryawan::Scheduled->value : ($data['status'] ?? StatusShiftKaryawan::Scheduled->value);

        return EmployeeShift::create([
            'employee_id' => $employee->id,
            'branch_id' => $data['branch_id'],
            'shift_date' => $data['shift_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => $status,
        ]);
    }

    /**
     * Perbarui shift karyawan.
     *
     * @param  array{shift_date?: string, start_time?: string, end_time?: string, status?: string}  $data
     *
     * @throws AuthorizationException|\InvalidArgumentException
     */
    public function updateShift(User $user, EmployeeShift $shift, array $data): EmployeeShift
    {
        $this->authorizeShiftManagement($user, $shift->branch_id);

        $targetDate = $data['shift_date'] ?? $shift->shift_date;
        $startTime = $data['start_time'] ?? $shift->start_time;
        $endTime = $data['end_time'] ?? $shift->end_time;
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        if ($start->diffInMinutes($end, false) > 480) {
            throw new \InvalidArgumentException('Durasi satu shift kerja tidak boleh melebihi 8 jam.');
        }

        // Cegah bentrok saat perbarui
        $clash = EmployeeShift::where('employee_id', $shift->employee_id)
            ->where('id', '!=', $shift->id)
            ->whereDate('shift_date', $targetDate)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($clash) {
            throw new \InvalidArgumentException('Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');
        }

        $shift->update([
            'shift_date' => $data['shift_date'] ?? $shift->shift_date,
            'start_time' => $data['start_time'] ?? $shift->start_time,
            'end_time' => $data['end_time'] ?? $shift->end_time,
            'status' => $data['status'] ?? $shift->status,
        ]);

        return $shift->fresh(['employee', 'branch']);
    }

    /**
     * Update status kehadiran shift.
     *
     * @throws AuthorizationException|\InvalidArgumentException
     */
    public function updateShiftStatus(User $user, EmployeeShift $shift, StatusShiftKaryawan $status): EmployeeShift
    {
        $this->authorizeShiftManagement($user, $shift->branch_id);

        $shiftDate = Carbon::parse($shift->shift_date)->startOfDay();
        if ($shiftDate->isFuture() && in_array($status, [StatusShiftKaryawan::Present, StatusShiftKaryawan::Late, StatusShiftKaryawan::Absent], true)) {
            throw new \InvalidArgumentException('Status kehadiran Hadir, Terlambat, atau Absen tidak dapat ditandai sebelum hari pelaksanaan shift.');
        }

        $shift->update(['status' => $status]);

        return $shift->fresh(['employee', 'branch']);
    }

    /**
     * Hapus shift karyawan.
     *
     * @throws AuthorizationException
     */
    public function deleteShift(User $user, EmployeeShift $shift): void
    {
        $this->authorizeShiftManagement($user, $shift->branch_id);

        $shift->delete();
    }
}
