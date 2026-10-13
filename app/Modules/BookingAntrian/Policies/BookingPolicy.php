<?php

namespace App\Modules\BookingAntrian\Policies;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\User;

class BookingPolicy
{
    /**
     * Otorisasi view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $this->canAccess($user, $booking);
    }

    /**
     * Otorisasi membuat booking baru.
     * Karyawan (employee) tidak memiliki wewenang membuat booking administratif.
     */
    public function create(User $user): bool
    {
        $roleName = $user->role?->name;

        if (in_array($roleName, ['employee', 'manager'], true)) {
            return false;
        }

        return in_array($roleName, ['owner', 'cashier', 'customer'], true);
    }

    /**
     * Otorisasi update the booking.
     * Karyawan (employee) tidak memiliki wewenang mengubah data administratif booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $this->canManage($user, $booking);
    }

    /**
     * Otorisasi cancel the booking.
     * Karyawan (employee) tidak memiliki wewenang membatalkan booking pelanggan.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $this->canManage($user, $booking);
    }

    /**
     * Cek hak akses booking.
     *
     * - Owner can access all bookings
     * - Manager hanya cabang yang dikelola
     * - Karyawan hanya booking yang ditugaskan
     * - Pelanggan hanya booking milik sendiri
     */
    protected function canAccess(User $user, Booking $booking): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $booking->branch_id)->exists()) {
            return true;
        }

        if ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');

            return $empId !== null && (int) $booking->employee_id === (int) $empId;
        }

        if ($booking->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Cek hak akses mengelola (edit/batal) data administratif booking.
     *
     * - Owner: semua booking
     * - Manager: cabang yang dikelola
     * - Pelanggan: booking miliknya sendiri
     * - Karyawan: ditolak
     */
    protected function canManage(User $user, Booking $booking): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $booking->branch_id)->exists()) {
            return true;
        }

        if ($roleName === 'employee') {
            return false;
        }

        if ($booking->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
