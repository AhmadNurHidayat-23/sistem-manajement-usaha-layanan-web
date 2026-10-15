<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingAntrian\BookingService;
use App\Services\Karyawan\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeMobileController extends Controller
{
    public function __construct(
        protected KaryawanService $karyawanService,
        protected BookingService $bookingService,
    ) {}

    /**
     * Dashboard ringkasan tugas dan shift karyawan.
     */
    public function dashboard(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $summary = $this->karyawanService->getDashboardSummaryForEmployee($user);

        $employee = $summary['employee'] ?? null;
        $performance = $summary['performance'] ?? [];
        $todayShift = $summary['todayShift'] ?? null;
        $upcomingShifts = $summary['upcomingShifts'] ?? collect();

        // Ambil tugas hari ini
        $todayTasks = collect();
        if ($employee) {
            $todayTasks = Booking::with(['user', 'service', 'branch'])
                ->where('employee_id', $employee->id)
                ->whereDate('booking_date', now()->toDateString())
                ->orderBy('booking_time')
                ->get();
        }

        // Hitung statistik tugas real-time
        $pendingTasksCount = $employee
            ? Booking::where('employee_id', $employee->id)->whereIn('status', ['pending', 'confirmed'])->count()
            : 0;
        $inProgressTasksCount = $employee
            ? Booking::where('employee_id', $employee->id)->where('status', 'in_progress')->count()
            : 0;
        $completedTasksCount = $employee
            ? Booking::where('employee_id', $employee->id)->where('status', 'completed')->count()
            : 0;
        $totalTasksCount = $employee
            ? Booking::where('employee_id', $employee->id)->count()
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee ? [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'branch' => $employee->branch ? [
                        'id' => $employee->branch->id,
                        'name' => $employee->branch->name,
                    ] : null,
                ] : null,
                'task_stats' => [
                    'total' => $totalTasksCount,
                    'pending' => $pendingTasksCount,
                    'in_progress' => $inProgressTasksCount,
                    'completed' => $completedTasksCount,
                ],
                'performance' => [
                    'total_bookings_assigned' => $totalTasksCount,
                    'completed_bookings' => $completedTasksCount,
                    'in_progress_bookings' => $inProgressTasksCount,
                    'pending_bookings' => $pendingTasksCount,
                    'total_commission' => (float) ($performance['total_commission'] ?? 0),
                    'attendance_rate' => (float) ($performance['attendance_rate'] ?? 100),
                ],
                'today_shift' => $todayShift ? [
                    'id' => $todayShift->id,
                    'shift_date' => $todayShift->shift_date?->format('Y-m-d'),
                    'start_time' => $todayShift->start_time,
                    'end_time' => $todayShift->end_time,
                    'status' => $todayShift->status?->value ?? 'scheduled',
                    'branch_name' => $todayShift->branch?->name,
                ] : null,
                'upcoming_shifts' => $upcomingShifts->map(fn ($s) => [
                    'id' => $s->id,
                    'shift_date' => $s->shift_date?->format('Y-m-d'),
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'status' => $s->status?->value ?? 'scheduled',
                    'branch_name' => $s->branch?->name,
                ]),
                'today_tasks' => $todayTasks->map(fn ($t) => [
                    'id' => $t->id,
                    'customer_name' => $t->user?->name ?? 'Pelanggan',
                    'service_name' => $t->service?->name ?? 'Layanan',
                    'booking_date' => $t->booking_date?->format('Y-m-d'),
                    'booking_time' => $t->booking_time,
                    'status' => $t->status->value,
                    'status_label' => $t->status->label(),
                ]),
            ],
        ]);
    }

    /**
     * Daftar tugas/antrian pekerjaan untuk karyawan.
     */
    public function tasks(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $employeeId = $user->employee?->id;

        if (! $employeeId) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Profil karyawan tidak ditemukan untuk akun ini.',
            ]);
        }

        $query = Booking::with(['user', 'service', 'branch', 'details.service'])
            ->where('employee_id', $employeeId);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->input('date'));
        }

        $tasks = $query->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'asc')
            ->paginate(30);

        $items = collect($tasks->items())->map(function ($t) {
            return [
                'id' => $t->id,
                'customer_name' => $t->user?->name ?? 'Pelanggan',
                'customer_phone' => $t->user?->phone,
                'service_name' => $t->service?->name ?? 'Layanan',
                'service_duration' => $t->service?->formatted_duration,
                'duration_formatted' => $t->service?->formatted_duration,
                'booking_date' => $t->booking_date?->format('Y-m-d'),
                'booking_time' => $t->booking_time,
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'branch_name' => $t->branch?->name,
                'notes' => $t->notes,
                'created_at' => $t->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Detail satu pekerjaan / booking.
     */
    public function showTask(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $task = Booking::with(['user', 'service', 'branch', 'details.service'])
            ->where('employee_id', $employeeId)
            ->find($id);

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $task->id,
                'customer' => [
                    'id' => $task->user?->id,
                    'name' => $task->user?->name,
                    'phone' => $task->user?->phone,
                    'email' => $task->user?->email,
                ],
                'service' => [
                    'id' => $task->service?->id,
                    'name' => $task->service?->name,
                    'description' => $task->service?->description,
                    'price' => (float) ($task->service?->price ?? 0),
                    'duration' => $task->service?->formatted_duration,
                    'duration_formatted' => $task->service?->formatted_duration,
                ],
                'branch' => [
                    'id' => $task->branch?->id,
                    'name' => $task->branch?->name,
                    'address' => $task->branch?->address,
                ],
                'booking_date' => $task->booking_date?->format('Y-m-d'),
                'booking_time' => $task->booking_time,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'notes' => $task->notes,
                'extra_services' => $task->details->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->service?->name,
                    'price' => (float) ($d->price ?? 0),
                ]),
                'created_at' => $task->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Perbarui status pekerjaan oleh karyawan (misal: in_progress, completed).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
        ]);

        /** @var User $user */
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $task = Booking::where('employee_id', $employeeId)->find($id);

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        $newStatus = $request->input('status');
        $this->bookingService->updateBookingStatus($user, $task, $newStatus);

        return response()->json([
            'success' => true,
            'message' => "Status tugas berhasil diperbarui menjadi {$task->fresh()->status->label()}.",
            'data' => [
                'id' => $task->id,
                'status' => $task->fresh()->status->value,
                'status_label' => $task->fresh()->status->label(),
            ],
        ]);
    }
}
