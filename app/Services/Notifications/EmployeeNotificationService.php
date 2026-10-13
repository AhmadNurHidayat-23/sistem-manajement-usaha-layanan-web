<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Order;
use App\Notifications\OperationalNotification;

class EmployeeNotificationService
{
    /**
     * Beri tahu karyawan tentang tugas baru yang sah (Appointment / Drop-off).
     */
    public function notifyNewTask(Employee $employee, Booking|Order $task): void
    {
        $user = $employee->user;
        if (! $user) {
            return;
        }

        $type = $task instanceof Booking ? 'booking' : 'order';
        $key = "task_assigned_{$type}_{$task->id}_{$employee->id}";
        if (NotificationHelper::hasKey($user, $key)) {
            return;
        }

        $service = $task->service?->name ?? 'Layanan';
        $customer = $task->user?->name ?? 'Pelanggan';

        if ($task instanceof Booking) {
            $time = is_string($task->booking_time) ? substr($task->booking_time, 0, 5) : $task->booking_time?->format('H:i');
            $msg = "Anda mendapatkan tugas {$service} untuk pelanggan {$customer}, pukul {$time}.";
        } else {
            $msg = "Anda mendapatkan tugas {$service} untuk pesanan #{$task->id} pelanggan {$customer}.";
        }

        $user->notify(new OperationalNotification(
            title: 'Tugas Baru Ditugaskan',
            message: $msg,
            category: 'task',
            statusLevel: 'info',
            actionUrl: route('employee.tugas.show', ['identifier' => $task->uuid]),
            actionLabel: 'Lihat Detail Tugas',
            details: ['type' => $type, 'id' => $task->id, 'uuid' => $task->uuid],
            idempotencyKey: $key,
        ));
    }

    /**
     * Beri tahu petugas lama bahwa tugas telah dialihkan ke karyawan lain.
     */
    public function notifyTaskReassigned(Employee $oldEmployee, Booking|Order $task): void
    {
        $user = $oldEmployee->user;
        if (! $user) {
            return;
        }

        $type = $task instanceof Booking ? 'booking' : 'order';
        $key = "task_reassigned_{$type}_{$task->id}_{$oldEmployee->id}_".($task->updated_at?->timestamp ?? time());
        if (NotificationHelper::hasKey($user, $key)) {
            return;
        }

        $service = $task->service?->name ?? 'Layanan';
        $label = $task instanceof Booking ? 'booking' : "pesanan #{$task->id}";

        $user->notify(new OperationalNotification(
            title: 'Pengalihan Tugas',
            message: "Tugas {$service} untuk {$label} telah dialihkan ke karyawan lain.",
            category: 'task',
            statusLevel: 'warning',
            actionUrl: route('queue.index'),
            actionLabel: 'Lihat Antrean Saya',
            details: ['type' => $type, 'id' => $task->id],
            idempotencyKey: $key,
        ));
    }

    /**
     * Beri tahu karyawan tentang perubahan jadwal tugas.
     */
    public function notifyScheduleChanged(Employee $employee, Booking $booking): void
    {
        $user = $employee->user;
        if (! $user) {
            return;
        }

        $key = "schedule_changed_booking_{$booking->id}_{$employee->id}_".($booking->updated_at?->timestamp ?? time());
        if (NotificationHelper::hasKey($user, $key)) {
            return;
        }

        $service = $booking->service?->name ?? 'Layanan';
        $date = $booking->booking_date?->format('d/m/Y');
        $time = is_string($booking->booking_time) ? substr($booking->booking_time, 0, 5) : $booking->booking_time?->format('H:i');

        $user->notify(new OperationalNotification(
            title: 'Perubahan Jadwal Tugas',
            message: "Jadwal tugas {$service} telah diubah ke tanggal {$date} pukul {$time}.",
            category: 'task',
            statusLevel: 'warning',
            actionUrl: route('employee.tugas.show', ['identifier' => $booking->uuid]),
            actionLabel: 'Lihat Detail Tugas',
            details: ['booking_id' => $booking->id, 'uuid' => $booking->uuid],
            idempotencyKey: $key,
        ));
    }

    /**
     * Beri tahu karyawan bahwa tugas telah dibatalkan.
     */
    public function notifyTaskCancelled(Employee $employee, Booking|Order $task): void
    {
        $user = $employee->user;
        if (! $user) {
            return;
        }

        $type = $task instanceof Booking ? 'booking' : 'order';
        $key = "task_cancelled_{$type}_{$task->id}_{$employee->id}";
        if (NotificationHelper::hasKey($user, $key)) {
            return;
        }

        $service = $task->service?->name ?? 'Layanan';
        $customer = $task->user?->name ?? 'Pelanggan';

        $user->notify(new OperationalNotification(
            title: 'Tugas Dibatalkan',
            message: "Tugas {$service} untuk pelanggan {$customer} telah dibatalkan.",
            category: 'task',
            statusLevel: 'error',
            actionUrl: route('queue.index'),
            actionLabel: 'Lihat Antrean Saya',
            details: ['type' => $type, 'id' => $task->id],
            idempotencyKey: $key,
        ));
    }

    /**
     * Beri tahu karyawan tentang perubahan status tugas miliknya.
     */
    public function notifyStatusChanged(Employee $employee, Booking|Order $task, string $status): void
    {
        $user = $employee->user;
        if (! $user) {
            return;
        }

        $type = $task instanceof Booking ? 'booking' : 'order';
        $key = "task_status_{$type}_{$task->id}_{$status}";
        if (NotificationHelper::hasKey($user, $key)) {
            return;
        }

        $service = $task->service?->name ?? 'Layanan';
        $label = ucfirst(str_replace('_', ' ', $status));

        $user->notify(new OperationalNotification(
            title: 'Update Status Tugas',
            message: "Status tugas {$service} diperbarui menjadi {$label}.",
            category: 'task',
            statusLevel: 'info',
            actionUrl: route('employee.tugas.show', ['identifier' => $task->uuid]),
            actionLabel: 'Lihat Detail Tugas',
            details: ['type' => $type, 'id' => $task->id, 'status' => $status],
            idempotencyKey: $key,
        ));
    }
}
