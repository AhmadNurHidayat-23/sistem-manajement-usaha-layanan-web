<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;

class PesananPolicy
{
    /**
     * Tentukan apakah pengguna dapat membuat pesanan drop-off baru.
     * Karyawan (employee) tidak memiliki wewenang membuat order drop-off.
     */
    public function create(User $user): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'employee') {
            return false;
        }

        return in_array($roleName, ['owner', 'manager', 'cashier', 'customer'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail pesanan (drop-off order).
     */
    public function view(User $user, Order $order): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($roleName === 'manager') {
            return $user->branches()->where('branches.id', $order->branch_id)->exists();
        }

        if ($roleName === 'cashier') {
            return $user->branches()->where('branches.id', $order->branch_id)->exists()
                || $user->employee?->branch_id === $order->branch_id;
        }

        if ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            if ($order->employee_id !== null && $empId && (int) $order->employee_id !== (int) $empId) {
                return false;
            }

            return $user->employee?->branch_id === $order->branch_id
                || $user->branches()->where('branches.id', $order->branch_id)->exists();
        }

        return $order->user_id === $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui status pesanan.
     */
    public function update(User $user, Order $order): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($roleName === 'manager') {
            return $user->branches()->where('branches.id', $order->branch_id)->exists();
        }

        if ($roleName === 'cashier') {
            return $user->branches()->where('branches.id', $order->branch_id)->exists()
                || $user->employee?->branch_id === $order->branch_id;
        }

        if ($roleName === 'employee') {
            $empId = $user->employee?->id ?? Employee::where('user_id', $user->id)->value('id');
            if ($order->employee_id !== null && $empId && (int) $order->employee_id !== (int) $empId) {
                return false;
            }

            return $user->employee?->branch_id === $order->branch_id
                || $user->branches()->where('branches.id', $order->branch_id)->exists();
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membatalkan pesanan.
     */
    public function cancel(User $user, Order $order): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if ($order->user_id === $user->id) {
            return in_array($order->status->value, ['pending', 'received'], true);
        }

        return false;
    }
}
