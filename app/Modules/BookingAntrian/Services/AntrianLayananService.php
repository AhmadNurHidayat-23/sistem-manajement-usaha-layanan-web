<?php

namespace App\Modules\BookingAntrian\Services;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AntrianLayananService
{
    /**
     * Ambil antrean janji temu (appointment) harian berdasarkan hak akses pengguna.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Booking>
     */
    public function getDailyQueue(User $user, string $date, array $filters = []): Collection
    {
        $query = Booking::query()
            ->with(['user', 'service', 'employee', 'branch'])
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [BookingStatus::Cancelled->value])
            ->orderBy('booking_time');

        $roleName = $user->role?->name;

        if ($roleName === 'employee') {
            $employeeId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $query->where('employee_id', $employeeId ?: 0);
        } elseif (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        return $query->get();
    }

    /**
     * Ambil antrean pesanan drop-off harian berdasarkan hak akses pengguna.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Order>
     */
    public function getDailyOrderQueue(User $user, string $date, array $filters = []): Collection
    {
        $query = Order::query()
            ->with(['user', 'service', 'employee', 'branch'])
            ->whereDate('created_at', $date)
            ->whereNotIn('status', [StatusPesanan::Cancelled->value])
            ->latest();

        $roleName = $user->role?->name;

        if ($roleName === 'employee') {
            $employeeId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            $query->where('employee_id', $employeeId ?: 0);
        } elseif (in_array($roleName, ['manager', 'cashier'], true)) {
            $branchIds = $user->branches()->pluck('branches.id');
            if ($branchIds->isEmpty() && $user->employee?->branch_id) {
                $branchIds = collect([$user->employee->branch_id]);
            }
            $query->whereIn('branch_id', $branchIds);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        return $query->get();
    }

    /**
     * Daftar cabang aktif.
     *
     * @return Collection<int, Branch>
     */
    public function getActiveBranches(): Collection
    {
        return Branch::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Ambil daftar tugas terpadu (Appointment + Drop-off) yang ditugaskan kepada pengguna.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getUnifiedDailyTasks(User $user, string $date, array $filters = []): \Illuminate\Support\Collection
    {
        $bookings = $this->getDailyQueue($user, $date, $filters);
        $orders = $this->getDailyOrderQueue($user, $date, $filters);

        $unified = collect();

        foreach ($bookings as $booking) {
            $statusVal = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;
            $statusUi = match ($statusVal) {
                BookingStatus::Confirmed->value => 'Menunggu',
                BookingStatus::InProgress->value => 'Dikerjakan',
                BookingStatus::Completed->value => 'Selesai',
                BookingStatus::Pending->value => 'Menunggu Konfirmasi',
                default => $booking->status->label(),
            };

            $canStart = $statusVal === BookingStatus::Confirmed->value;
            $canComplete = $statusVal === BookingStatus::InProgress->value;
            $isFinished = $statusVal === BookingStatus::Completed->value;

            $unified->push([
                'id' => $booking->id,
                'task_type' => 'Appointment',
                'model_type' => 'booking',
                'time' => Carbon::parse($booking->booking_time)->format('H:i'),
                'customer_name' => $booking->user?->name ?? 'Pelanggan',
                'service_name' => $booking->service?->name ?? 'Layanan',
                'branch_name' => $booking->branch?->display_name ?? 'Usaha',
                'status_raw' => $statusVal,
                'status_ui' => $statusUi,
                'can_start' => $canStart,
                'can_complete' => $canComplete,
                'is_finished' => $isFinished,
                'detail_url' => ($user->role?->name === 'employee')
                    ? route('employee.tugas.show', $booking->uuid ?? $booking->id)
                    : route('bookings.show', $booking),
                'secure_detail_url' => route('employee.tugas.show', $booking->uuid ?? $booking->id),
                'action_url' => route('bookings.update-status', $booking),
                'next_status' => $canStart ? BookingStatus::InProgress->value : ($canComplete ? BookingStatus::Completed->value : null),
                'action_label' => $canStart ? 'Mulai Pengerjaan' : ($canComplete ? 'Tandai Selesai' : null),
                'priority' => match ($statusUi) {
                    'Dikerjakan' => 1,
                    'Menunggu' => 2,
                    'Menunggu Konfirmasi' => 3,
                    'Selesai' => 4,
                    default => 5,
                },
                'model' => $booking,
            ]);
        }

        foreach ($orders as $order) {
            $statusVal = $order->status instanceof StatusPesanan ? $order->status->value : (string) $order->status;
            $statusUi = match ($statusVal) {
                StatusPesanan::Received->value => 'Menunggu',
                StatusPesanan::Processing->value => 'Dikerjakan',
                StatusPesanan::Ready->value, StatusPesanan::Completed->value => 'Selesai',
                StatusPesanan::Pending->value => 'Menunggu Konfirmasi',
                default => $order->status->label(),
            };

            $canStart = $statusVal === StatusPesanan::Received->value;
            $canComplete = $statusVal === StatusPesanan::Processing->value;
            $isFinished = in_array($statusVal, [StatusPesanan::Ready->value, StatusPesanan::Completed->value], true);

            $time = $order->estimated_finish_at
                ? $order->estimated_finish_at->format('H:i')
                : $order->created_at->format('H:i');

            $unified->push([
                'id' => $order->id,
                'task_type' => 'Drop-off',
                'model_type' => 'order',
                'time' => $time,
                'customer_name' => $order->user?->name ?? 'Pelanggan',
                'service_name' => $order->service?->name ?? 'Layanan',
                'branch_name' => $order->branch?->display_name ?? 'Usaha',
                'status_raw' => $statusVal,
                'status_ui' => $statusUi,
                'can_start' => $canStart,
                'can_complete' => $canComplete,
                'is_finished' => $isFinished,
                'detail_url' => ($user->role?->name === 'employee')
                    ? route('employee.tugas.show', $order->uuid ?? $order->id)
                    : route('orders.show', $order),
                'secure_detail_url' => route('employee.tugas.show', $order->uuid ?? $order->id),
                'action_url' => route('orders.update-status', $order),
                'next_status' => $canStart ? StatusPesanan::Processing->value : ($canComplete ? StatusPesanan::Completed->value : null),
                'action_label' => $canStart ? 'Mulai Pengerjaan' : ($canComplete ? 'Tandai Selesai' : null),
                'priority' => match ($statusUi) {
                    'Dikerjakan' => 1,
                    'Menunggu' => 2,
                    'Menunggu Konfirmasi' => 3,
                    'Selesai' => 4,
                    default => 5,
                },
                'model' => $order,
            ]);
        }

        return $unified->sort(function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }

            return strcmp($a['time'], $b['time']);
        })->values();
    }

    /**
     * Ambil riwayat tugas yang telah selesai dari hari sebelumnya.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getPastTasks(User $user, int $limit = 25): \Illuminate\Support\Collection
    {
        $employeeId = $user->role?->name === 'employee'
            ? ($user->employee?->id ?? Employee::where('user_id', $user->id)->value('id'))
            : null;

        $bookingsQuery = Booking::query()
            ->with(['user', 'service', 'branch'])
            ->whereDate('booking_date', '<', Carbon::today())
            ->where('status', BookingStatus::Completed->value);

        if ($employeeId) {
            $bookingsQuery->where('employee_id', $employeeId);
        }

        $ordersQuery = Order::query()
            ->with(['user', 'service', 'branch'])
            ->whereDate('created_at', '<', Carbon::today())
            ->whereIn('status', [StatusPesanan::Completed->value, StatusPesanan::Ready->value]);

        if ($employeeId) {
            $ordersQuery->where('employee_id', $employeeId);
        }

        $pastBookings = $bookingsQuery->latest('booking_date')->limit($limit)->get()->map(fn ($b) => [
            'id' => $b->id,
            'task_type' => 'Appointment',
            'model_type' => 'booking',
            'time' => Carbon::parse($b->booking_time)->format('H:i'),
            'date' => Carbon::parse($b->booking_date)->translatedFormat('l, d F Y'),
            'customer_name' => $b->user?->name ?? 'Pelanggan',
            'service_name' => $b->service?->name ?? 'Layanan',
            'branch_name' => $b->branch?->display_name ?? 'Usaha',
            'status_ui' => 'Selesai',
            'detail_url' => route('bookings.show', $b),
            'model' => $b,
        ]);

        $pastOrders = $ordersQuery->latest('created_at')->limit($limit)->get()->map(fn ($o) => [
            'id' => $o->id,
            'task_type' => 'Drop-off',
            'model_type' => 'order',
            'time' => $o->created_at->format('H:i'),
            'date' => $o->created_at->translatedFormat('l, d F Y'),
            'customer_name' => $o->user?->name ?? 'Pelanggan',
            'service_name' => $o->service?->name ?? 'Layanan',
            'branch_name' => $o->branch?->display_name ?? 'Usaha',
            'status_ui' => 'Selesai',
            'detail_url' => route('orders.show', $o),
            'model' => $o,
        ]);

        return $pastBookings->concat($pastOrders)->sortByDesc('date')->values();
    }
}
