<?php

namespace Database\Seeders;

use App\Enums\StatusShiftKaryawan;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\EmployeeSkill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Jalankan seeder karyawan, keahlian, dan jadwal kerja pada tiap cabang.
     */
    public function run(): void
    {
        $branchA = Branch::where('name', 'like', '%Pusat%')->firstOrFail();
        $branchB = Branch::where('name', 'like', '%Selatan%')->firstOrFail();
        $branchC = Branch::where('name', 'like', '%Barat%')->firstOrFail();
        $employeeUser = User::whereIn('email', ['karyawan@simaul.com', 'employee@example.com'])->firstOrFail();

        // 1. Karyawan Cabang A - Pusat (Barber & Stylist: Dedi Kurniawan)
        $employeeA = Employee::firstOrCreate(
            ['user_id' => $employeeUser->id],
            [
                'branch_id' => $branchA->id,
                'name' => 'Dedi Kurniawan',
                'phone' => '08110000004',
                'status' => 'active',
            ]
        );

        // Keahlian Barbershop
        $skillsA = ['Executive Haircut', 'Beard Styling & Shaving', 'Scalp Treatment'];
        foreach ($skillsA as $skill) {
            EmployeeSkill::firstOrCreate([
                'employee_id' => $employeeA->id,
                'skill_name' => $skill,
            ]);
        }

        // Shift Kerja Cabang A
        EmployeeShift::firstOrCreate(
            [
                'employee_id' => $employeeA->id,
                'shift_date' => Carbon::today()->toDateString(),
            ],
            [
                'branch_id' => $branchA->id,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'status' => StatusShiftKaryawan::Scheduled,
            ]
        );

        EmployeeShift::firstOrCreate(
            [
                'employee_id' => $employeeA->id,
                'shift_date' => Carbon::tomorrow()->toDateString(),
            ],
            [
                'branch_id' => $branchA->id,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'status' => StatusShiftKaryawan::Scheduled,
            ]
        );

        $employeeBUser = User::whereIn('email', ['karyawan.laundry@simaul.com', 'employee.selatan@example.com'])->first();
        $employeeCUser = User::whereIn('email', ['karyawan.autocare@simaul.com', 'employee.barat@example.com'])->first();

        // 2. Karyawan Cabang B - Laundry (Agus Santoso)
        $employeeB = Employee::firstOrCreate(
            ['name' => 'Agus Santoso', 'branch_id' => $branchB->id],
            [
                'user_id' => $employeeBUser?->id,
                'phone' => '08110000005',
                'status' => 'active',
            ]
        );
        if (! $employeeB->user_id && $employeeBUser) {
            $employeeB->update(['user_id' => $employeeBUser->id]);
        }

        $skillsB = ['Deep Clean Sepatu Sneakers', 'Pencucian Jas & Gaun', 'Setrika Uap Presisi'];
        foreach ($skillsB as $skill) {
            EmployeeSkill::firstOrCreate([
                'employee_id' => $employeeB->id,
                'skill_name' => $skill,
            ]);
        }

        EmployeeShift::firstOrCreate(
            [
                'employee_id' => $employeeB->id,
                'shift_date' => Carbon::today()->toDateString(),
            ],
            [
                'branch_id' => $branchB->id,
                'start_time' => '08:00',
                'end_time' => '16:00',
                'status' => StatusShiftKaryawan::Scheduled,
            ]
        );

        // 3. Karyawan Cabang C - Auto Care (Fajar Hidayat)
        $employeeC = Employee::firstOrCreate(
            ['name' => 'Fajar Hidayat', 'branch_id' => $branchC->id],
            [
                'user_id' => $employeeCUser?->id,
                'phone' => '08110000006',
                'status' => 'active',
            ]
        );
        if (! $employeeC->user_id && $employeeCUser) {
            $employeeC->update(['user_id' => $employeeCUser->id]);
        }

        $skillsC = ['Exterior Polishing', 'Interior Steam Sterilization', 'Nano Ceramic Coating'];
        foreach ($skillsC as $skill) {
            EmployeeSkill::firstOrCreate([
                'employee_id' => $employeeC->id,
                'skill_name' => $skill,
            ]);
        }

        EmployeeShift::firstOrCreate(
            [
                'employee_id' => $employeeC->id,
                'shift_date' => Carbon::today()->toDateString(),
            ],
            [
                'branch_id' => $branchC->id,
                'start_time' => '08:00',
                'end_time' => '16:00',
                'status' => StatusShiftKaryawan::Scheduled,
            ]
        );
    }
}
