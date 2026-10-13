<?php

namespace Database\Seeders;

use App\Enums\StatusShiftKaryawan;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoEmployeeShiftSeeder extends Seeder
{
    /**
     * Lengkapi jadwal shift untuk seluruh karyawan demo aktif (14 hari ke depan).
     * Idempoten: hanya menambahkan jadwal yang belum ada, menjaga data historis.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->error('Seeder ini hanya aman dijalankan pada environment local atau testing.');

            return;
        }

        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $daysRange = 14;

        // 1. Karyawan Barbershop & Studio: Dedi Kurniawan (Cabang ID: 1)
        $emp1 = Employee::where('name', 'Dedi Kurniawan')->first();
        if ($emp1) {
            $this->seedShiftsForEmployee(
                employee: $emp1,
                startDate: $today,
                days: $daysRange,
                startTime: '09:00:00',
                endTime: '17:00:00',
                offDaysOfWeek: [1] // Senin libur (hari tutup Barbershop)
            );
        }

        // 2. Karyawan Laundry & Shoe Care: Agus Santoso (Cabang ID: 2)
        $emp2 = Employee::where('name', 'Agus Santoso')->first();
        if ($emp2) {
            $this->seedShiftsForEmployee(
                employee: $emp2,
                startDate: $today,
                days: $daysRange,
                startTime: '08:00:00',
                endTime: '16:00:00',
                offDaysOfWeek: [7] // Minggu libur (rest day karyawan)
            );
        }

        // 3. Karyawan Auto Care & Detailing: Fajar Hidayat (Cabang ID: 3)
        $emp3 = Employee::where('name', 'Fajar Hidayat')->first();
        if ($emp3) {
            $this->seedShiftsForEmployee(
                employee: $emp3,
                startDate: $today,
                days: $daysRange,
                startTime: '08:30:00',
                endTime: '16:30:00',
                offDaysOfWeek: [7] // Minggu libur (hari tutup Auto Care)
            );
        }
    }

    /**
     * Seed shifts idempotently for an employee.
     *
     * @param  array<int>  $offDaysOfWeek  ISO day-of-week (1=Mon, 7=Sun)
     */
    protected function seedShiftsForEmployee(
        Employee $employee,
        Carbon $startDate,
        int $days,
        string $startTime,
        string $endTime,
        array $offDaysOfWeek
    ): void {
        $branch = $employee->branch;
        if (! $branch) {
            return;
        }

        for ($i = 0; $i <= $days; $i++) {
            $targetDate = $startDate->copy()->addDays($i);
            $dayOfWeek = (int) $targetDate->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $dateStr = $targetDate->toDateString();

            // Cek apakah hari libur karyawan atau cabang tutup
            if (in_array($dayOfWeek, $offDaysOfWeek, true)) {
                continue;
            }

            if (! $branch->isOpenOnDate($dateStr)) {
                continue;
            }

            // Cek jadwal existing (idempoten)
            $existing = EmployeeShift::where('employee_id', $employee->id)
                ->whereDate('shift_date', $dateStr)
                ->first();

            if (! $existing) {
                EmployeeShift::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $branch->id,
                    'shift_date' => $dateStr,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => StatusShiftKaryawan::Scheduled,
                ]);
            }
        }
    }
}
