<?php

namespace App\Modules\BookingAntrian\Services;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Services\OperationalNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class StatusPekerjaanService
{
    /**
     * Matriks transisi yang valid untuk Booking (Appointment).
     *
     * @var array<string, array<string>>
     */
    protected array $validBookingTransitions = [
        'pending' => ['confirmed', 'in_progress', 'cancelled'],
        'confirmed' => ['in_progress', 'completed', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Matriks transisi yang valid untuk Order (Drop-off).
     *
     * @var array<string, array<string>>
     */
    protected array $validOrderTransitions = [
        'pending' => ['received', 'processing', 'cancelled'],
        'received' => ['processing', 'ready', 'cancelled'],
        'processing' => ['ready', 'completed', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Perbarui status booking dengan validasi transisi dan otorisasi.
     *
     * @throws AuthorizationException|InvalidArgumentException
     */
    public function updateBookingStatus(User $user, Booking $booking, string $targetStatus): Booking
    {
        $this->authorizeBookingUpdate($user, $booking);

        $currentStatusVal = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;

        // Idempoten jika status sama
        if ($currentStatusVal === $targetStatus) {
            return $booking;
        }

        $allowed = $this->validBookingTransitions[$currentStatusVal] ?? [];
        if (! in_array($targetStatus, $allowed, true)) {
            throw new InvalidArgumentException("Transisi status booking dari '{$currentStatusVal}' ke '{$targetStatus}' tidak diizinkan.");
        }

        if ($user->role?->name === 'employee' && $targetStatus === BookingStatus::Cancelled->value) {
            throw new AuthorizationException('Karyawan tidak memiliki wewenang untuk membatalkan booking.');
        }

        $booking->update(['status' => $targetStatus]);

        $fresh = $booking->fresh(['branch', 'service', 'employee', 'user']);
        if ($fresh->user) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($fresh->user, $fresh, $targetStatus));
        }
        if ($fresh->employee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeStatusChanged($fresh->employee, $fresh, $targetStatus));
        }

        return $fresh;
    }

    /**
     * Perbarui status order drop-off dengan validasi transisi dan otorisasi.
     *
     * @throws AuthorizationException|InvalidArgumentException
     */
    public function updateOrderStatus(User $user, Order $order, string $targetStatus): Order
    {
        $this->authorizeOrderUpdate($user, $order);

        $currentStatusVal = $order->status instanceof StatusPesanan ? $order->status->value : (string) $order->status;

        // Idempoten jika status sama
        if ($currentStatusVal === $targetStatus) {
            return $order;
        }

        $allowed = $this->validOrderTransitions[$currentStatusVal] ?? [];
        if (! in_array($targetStatus, $allowed, true)) {
            throw new InvalidArgumentException("Transisi status order dari '{$currentStatusVal}' ke '{$targetStatus}' tidak diizinkan.");
        }

        if ($user->role?->name === 'employee' && $targetStatus === StatusPesanan::Cancelled->value) {
            throw new AuthorizationException('Karyawan tidak memiliki wewenang untuk membatalkan order.');
        }

        $changes = ['status' => $targetStatus];

        if ($targetStatus === StatusPesanan::Processing->value && is_null($order->estimated_finish_at)) {
            $service = $order->service;
            $minutes = $service?->estimated_duration ?? 60;
            $changes['estimated_finish_at'] = now()->addMinutes($minutes);
        }

        $order->update($changes);

        $fresh = $order->fresh(['branch', 'service', 'employee', 'user']);
        if ($fresh->user) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($fresh->user, $fresh, $targetStatus));
        }
        if ($fresh->employee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeStatusChanged($fresh->employee, $fresh, $targetStatus));
        }

        return $fresh;
    }

    /**
     * Otorisasi update status booking.
     *
     * @throws AuthorizationException
     */
    public function authorizeBookingUpdate(User $user, Booking $booking): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $booking->branch_id)->exists()) {
            return;
        }

        if ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            if ($empId && (int) $booking->employee_id === (int) $empId) {
                return;
            }
        }

        throw new AuthorizationException('Anda tidak berhak mengubah status booking ini.');
    }

    /**
     * Otorisasi update status order drop-off.
     *
     * @throws AuthorizationException
     */
    public function authorizeOrderUpdate(User $user, Order $order): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $order->branch_id)->exists()) {
            return;
        }

        if ($roleName === 'cashier') {
            if ($user->branches()->where('branches.id', $order->branch_id)->exists() || $user->employee?->branch_id === $order->branch_id) {
                return;
            }
        }

        if ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            if ($empId) {
                if ($order->employee_id !== null && (int) $order->employee_id === (int) $empId) {
                    return;
                }
                if ($order->employee_id === null && $user->employee?->branch_id === $order->branch_id) {
                    return;
                }
            }
        }

        throw new AuthorizationException('Anda tidak berhak mengubah status order ini.');
    }
}
