<?php

namespace App\Modules\Karyawan\Services;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\StatusPesanan;
use App\Enums\StatusShiftKaryawan;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class KinerjaKaryawanService
{
    /**
     * Ringkasan performa karyawan.
     *
     * @return array<string, mixed>
     */
    public function getEmployeePerformanceSummary(Employee $employee): array
    {
        // Statistik booking (1 query agregasi)
        $bookingCounts = $employee->bookings()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as in_progress
            ', [BookingStatus::Completed->value, BookingStatus::Confirmed->value, BookingStatus::InProgress->value])
            ->first();

        $totalBookingsAssigned = (int) ($bookingCounts?->total ?? 0);
        $completedBookings = (int) ($bookingCounts?->completed ?? 0);
        $inProgressBookings = (int) ($bookingCounts?->in_progress ?? 0);

        // Statistik shift (1 query terkelompok)
        $shiftCounts = $employee->shifts()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $presentShifts = (int) ($shiftCounts[StatusShiftKaryawan::Present->value] ?? 0);
        $lateShifts = (int) ($shiftCounts[StatusShiftKaryawan::Late->value] ?? 0);
        $absentShifts = (int) ($shiftCounts[StatusShiftKaryawan::Absent->value] ?? 0);
        $offShifts = (int) ($shiftCounts[StatusShiftKaryawan::Off->value] ?? 0);
        $scheduledShifts = (int) ($shiftCounts[StatusShiftKaryawan::Scheduled->value] ?? 0);
        $totalShifts = array_sum($shiftCounts);

        $workedShifts = $presentShifts + $lateShifts;
        $evaluableShifts = $workedShifts + $absentShifts;
        $attendanceRate = $evaluableShifts > 0 ? round(($workedShifts / $evaluableShifts) * 100, 1) : 100;

        // Statistik komisi (1 query terkelompok)
        $commissionSums = $employee->commissions()
            ->selectRaw('status, SUM(commission_amount) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pendingCommission = (float) ($commissionSums[CommissionStatus::Pending->value] ?? 0);
        $approvedCommission = (float) ($commissionSums[CommissionStatus::Approved->value] ?? 0);
        $paidCommission = (float) ($commissionSums[CommissionStatus::Paid->value] ?? 0);
        $totalCommissionEarned = $pendingCommission + $approvedCommission + $paidCommission;

        // Aktivitas terbaru
        $recentShifts = $employee->shifts()
            ->with('branch')
            ->orderBy('shift_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(7)
            ->get();

        $recentCommissions = $employee->commissions()
            ->with(['service', 'transaction'])
            ->latest()
            ->limit(10)
            ->get();

        $recentBookings = $employee->bookings()
            ->with(['service', 'user', 'branch'])
            ->latest('booking_date')
            ->limit(5)
            ->get();

        return [
            'total_bookings_assigned' => $totalBookingsAssigned,
            'completed_bookings' => $completedBookings,
            'in_progress_bookings' => $inProgressBookings,
            'total_shifts' => $totalShifts,
            'present_shifts' => $presentShifts,
            'late_shifts' => $lateShifts,
            'absent_shifts' => $absentShifts,
            'off_shifts' => $offShifts,
            'scheduled_shifts' => $scheduledShifts,
            'attendance_rate' => $attendanceRate,
            'pending_commission' => $pendingCommission,
            'approved_commission' => $approvedCommission,
            'paid_commission' => $paidCommission,
            'total_commission' => $totalCommissionEarned,
            'recent_shifts' => $recentShifts,
            'recent_commissions' => $recentCommissions,
            'recent_bookings' => $recentBookings,
        ];
    }

    /**
     * Ringkasan dashboard karyawan.
     *
     * @return array<string, mixed>
     */
    public function getDashboardSummaryForEmployee(User $user): array
    {
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return [
                'employee' => null,
                'performance' => [
                    'completed_bookings' => 0,
                    'in_progress_bookings' => 0,
                    'attendance_rate' => 100,
                    'pending_commission' => 0,
                    'approved_commission' => 0,
                    'paid_commission' => 0,
                    'total_commission' => 0,
                    'recent_shifts' => collect(),
                    'recent_commissions' => collect(),
                    'recent_bookings' => collect(),
                ],
                'todayShift' => null,
                'upcomingShifts' => collect(),
                'todayTasks' => collect(),
                'historyTasks' => collect(),
            ];
        }

        $employee->load(['branch', 'skills']);
        $performance = $this->getEmployeePerformanceSummary($employee);

        $todayShift = $employee->shifts()
            ->whereDate('shift_date', Carbon::today())
            ->first();

        $upcomingShifts = $employee->shifts()
            ->whereDate('shift_date', '>=', Carbon::today())
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // Tugas hari ini (Appointment + Drop-off yang ditugaskan khusus karyawan ini)
        $todayBookings = $employee->bookings()
            ->with(['service', 'user', 'branch'])
            ->whereDate('booking_date', Carbon::today())
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->orderBy('booking_time')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'type' => 'Appointment',
                'service_name' => $b->service?->name ?? 'Layanan',
                'branch_name' => $b->branch?->display_name ?? '-',
                'customer_name' => $b->user?->name ?? 'Pelanggan',
                'time' => Carbon::parse($b->booking_time)->format('H:i'),
                'date' => Carbon::parse($b->booking_date)->translatedFormat('d M Y'),
                'status' => $b->status,
                'detail_url' => route('bookings.show', $b),
            ]);

        $todayOrders = $employee->orders()
            ->with(['service', 'user', 'branch'])
            ->where(function ($q) {
                $q->whereDate('created_at', Carbon::today())
                    ->orWhereIn('status', [StatusPesanan::Received, StatusPesanan::Processing]);
            })
            ->whereNotIn('status', [StatusPesanan::Cancelled->value])
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'type' => 'Drop-off',
                'service_name' => $o->service?->name ?? 'Layanan',
                'branch_name' => $o->branch?->display_name ?? '-',
                'customer_name' => $o->user?->name ?? 'Pelanggan',
                'time' => $o->estimated_finish_at ? $o->estimated_finish_at->format('H:i') : $o->created_at->format('H:i'),
                'date' => $o->created_at->translatedFormat('d M Y'),
                'status' => $o->status,
                'detail_url' => route('orders.show', $o),
            ]);

        $todayTasks = $todayBookings->concat($todayOrders);

        // Riwayat tugas sebelumnya
        $pastBookings = $employee->bookings()
            ->with(['service', 'user', 'branch'])
            ->whereDate('booking_date', '<', Carbon::today())
            ->latest('booking_date')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'type' => 'Appointment',
                'service_name' => $b->service?->name ?? 'Layanan',
                'branch_name' => $b->branch?->display_name ?? '-',
                'customer_name' => $b->user?->name ?? 'Pelanggan',
                'time' => Carbon::parse($b->booking_time)->format('H:i'),
                'date' => Carbon::parse($b->booking_date)->translatedFormat('d M Y'),
                'status' => $b->status,
                'detail_url' => route('bookings.show', $b),
            ]);

        $pastOrders = $employee->orders()
            ->with(['service', 'user', 'branch'])
            ->whereDate('created_at', '<', Carbon::today())
            ->whereIn('status', [StatusPesanan::Completed, StatusPesanan::Cancelled])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'type' => 'Drop-off',
                'service_name' => $o->service?->name ?? 'Layanan',
                'branch_name' => $o->branch?->display_name ?? '-',
                'customer_name' => $o->user?->name ?? 'Pelanggan',
                'time' => $o->created_at->format('H:i'),
                'date' => $o->created_at->translatedFormat('d M Y'),
                'status' => $o->status,
                'detail_url' => route('orders.show', $o),
            ]);

        $historyTasks = $pastBookings->concat($pastOrders);

        return [
            'employee' => $employee,
            'performance' => $performance,
            'todayShift' => $todayShift,
            'upcomingShifts' => $upcomingShifts,
            'todayTasks' => $todayTasks,
            'historyTasks' => $historyTasks,
        ];
    }
}
