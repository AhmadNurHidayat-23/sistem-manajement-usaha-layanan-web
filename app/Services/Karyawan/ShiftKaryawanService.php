<?php

namespace App\Services\Karyawan;

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

        $today = Carbon::today('Asia/Jakarta')->toDateString();
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
            if ($filters['status'] === StatusShiftKaryawan::Ended->value) {
                $now = Carbon::now('Asia/Jakarta');
                $nowDate = $now->toDateString();
                $nowTime = $now->toTimeString();

                $query->where('status', StatusShiftKaryawan::Scheduled->value)
                    ->where(function ($q) use ($nowDate, $nowTime) {
                        $q->whereDate('shift_date', '<', $nowDate)
                            ->orWhere(function ($sub) use ($nowDate, $nowTime) {
                                $sub->whereDate('shift_date', $nowDate)
                                    ->where('end_time', '<=', $nowTime);
                            });
                    });
            } else {
                $query->where('status', $filters['status']);
            }
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
     * Validasi durasi dan bentrok jadwal shift untuk karyawan tertentu.
     *
     * @param  string  $shiftDate  Format Y-m-d
     * @param  string  $startTimeStr  Format H:i atau H:i:s
     * @param  string  $endTimeStr  Format H:i atau H:i:s
     * @param  int|null  $excludeShiftId  ID shift yang sedang diedit (jika ada)
     * @return array{shift_date: string, start_time: string, end_time: string, start_datetime: Carbon, end_datetime: Carbon}
     *
     * @throws \InvalidArgumentException
     */
    public function validateShiftSchedule(
        int $employeeId,
        string $shiftDate,
        string $startTimeStr,
        string $endTimeStr,
        ?int $excludeShiftId = null
    ): array {
        $dateFormatted = Carbon::parse($shiftDate, 'Asia/Jakarta')->format('Y-m-d');
        $startTime = Carbon::parse($startTimeStr)->format('H:i:s');
        $endTime = Carbon::parse($endTimeStr)->format('H:i:s');

        $startDateTime = Carbon::parse("{$dateFormatted} {$startTime}", 'Asia/Jakarta');
        $endDateTime = Carbon::parse("{$dateFormatted} {$endTime}", 'Asia/Jakarta');

        // Penanganan shift lintas hari / melewati tengah malam (+1 hari)
        if ($endDateTime->lte($startDateTime)) {
            $endDateTime->addDay();
        }

        $durationMinutes = $startDateTime->diffInMinutes($endDateTime, false);
        if ($durationMinutes <= 0) {
            throw new \InvalidArgumentException('Jam selesai tidak boleh sama dengan jam mulai.');
        }
        if ($durationMinutes > 480) {
            throw new \InvalidArgumentException('Durasi satu shift kerja tidak boleh melebihi 8 jam.');
        }

        // Ambil potensi shift karyawan yang sama dalam rentang -1 hari s/d +1 hari
        // untuk mendeteksi tumpang tindih waktu lengkap (termasuk lintas tengah malam)
        $searchStart = $startDateTime->copy()->subDay()->format('Y-m-d');
        $searchEnd = $endDateTime->copy()->addDay()->format('Y-m-d');

        $existingShifts = EmployeeShift::query()
            ->where('employee_id', $employeeId)
            ->when($excludeShiftId, fn ($q) => $q->where('id', '!=', $excludeShiftId))
            ->whereBetween('shift_date', [$searchStart, $searchEnd])
            ->get();

        foreach ($existingShifts as $existing) {
            $exDate = $existing->shift_date instanceof \DateTimeInterface
                ? $existing->shift_date->format('Y-m-d')
                : substr((string) $existing->shift_date, 0, 10);

            $exStartTime = substr((string) $existing->start_time, 0, 8);
            $exEndTime = substr((string) $existing->end_time, 0, 8);

            $exStart = Carbon::parse("{$exDate} {$exStartTime}", 'Asia/Jakarta');
            $exEnd = Carbon::parse("{$exDate} {$exEndTime}", 'Asia/Jakarta');
            if ($exEnd->lte($exStart)) {
                $exEnd->addDay();
            }

            // Bentrok terjadi HANYA jika rentang waktu [start, end) saling tumpang tindih
            if ($startDateTime->lt($exEnd) && $endDateTime->gt($exStart)) {
                throw new \InvalidArgumentException('Jadwal bentrok dengan shift lain karyawan ini pada jam tersebut.');
            }
        }

        return [
            'shift_date' => $dateFormatted,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
        ];
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

        $employee = Employee::findOrFail($data['employee_id']);

        $validatedSchedule = $this->validateShiftSchedule(
            $employee->id,
            $data['shift_date'],
            $data['start_time'],
            $data['end_time']
        );

        $status = $data['status'] ?? StatusShiftKaryawan::Scheduled->value;
        if ($validatedSchedule['start_datetime']->isFuture() && in_array($status, [StatusShiftKaryawan::Present->value, StatusShiftKaryawan::Late->value, StatusShiftKaryawan::Absent->value], true)) {
            $status = StatusShiftKaryawan::Scheduled->value;
        }

        return EmployeeShift::create([
            'employee_id' => $employee->id,
            'branch_id' => $data['branch_id'],
            'shift_date' => $validatedSchedule['shift_date'],
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

        $targetDateRaw = $data['shift_date'] ?? $shift->shift_date;
        $targetDate = $targetDateRaw instanceof \DateTimeInterface
            ? $targetDateRaw->format('Y-m-d')
            : (string) $targetDateRaw;

        $startTimeRaw = $data['start_time'] ?? $shift->start_time;
        $endTimeRaw = $data['end_time'] ?? $shift->end_time;

        $validatedSchedule = $this->validateShiftSchedule(
            $shift->employee_id,
            $targetDate,
            (string) $startTimeRaw,
            (string) $endTimeRaw,
            $shift->id
        );

        $shift->update([
            'shift_date' => $validatedSchedule['shift_date'],
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

        $now = Carbon::now('Asia/Jakarta');
        $shiftStart = $shift->start_date_time;

        // Batasi aksi absensi jika waktu shift belum tiba (tanggal atau jam di masa depan)
        if ($now->lt($shiftStart) && in_array($status, [StatusShiftKaryawan::Present, StatusShiftKaryawan::Late, StatusShiftKaryawan::Absent], true)) {
            if ($shiftStart->toDateString() > $now->toDateString()) {
                throw new \InvalidArgumentException('Status kehadiran Hadir, Terlambat, atau Absen tidak dapat ditandai sebelum hari pelaksanaan shift.');
            }
            throw new \InvalidArgumentException('Status kehadiran Hadir, Terlambat, atau Absen tidak dapat ditandai sebelum shift dimulai.');
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
