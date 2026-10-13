<?php

namespace App\Services\Notifications;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationHelper
{
    /**
     * Dapatkan daftar staf cabang aktif berdasarkan peran (cashier / manager).
     *
     * @return Collection<int, User>
     */
    public static function getBranchStaff(int $branchId, string $roleName): Collection
    {
        return User::whereHas('role', fn ($q) => $q->where('name', $roleName))
            ->where(function ($q) use ($branchId) {
                $q->whereHas('branches', fn ($b) => $b->where('branches.id', $branchId))
                    ->orWhereHas('employee', fn ($e) => $e->where('branch_id', $branchId));
            })
            ->get();
    }

    /**
     * Periksa apakah pengguna telah menerima notifikasi dengan kunci idempotensi tersebut.
     */
    public static function hasKey(User $user, string $key): bool
    {
        return $user->notifications()
            ->where('data', 'like', "%\"idempotency_key\":\"{$key}\"%")
            ->exists();
    }
}
