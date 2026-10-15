<?php

namespace App\Services\Cabang;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CabangService
{
    /**
     * Daftar cabang yang dapat diakses pengguna.
     */
    public function getBranchesForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return Branch::query()->latest()->paginate($perPage);
        }

        if ($roleName === 'manager') {
            return $user->branches()->latest()->paginate($perPage);
        }

        return Branch::query()->whereRaw('1 = 0')->paginate($perPage);
    }

    /**
     * Otorisasi akses cabang.
     *
     * @throws AuthorizationException
     */
    public function authorizeBranchAccess(User $user, Branch $branch): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager' && $user->branches()->where('branches.id', $branch->id)->exists()) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki akses ke data cabang ini.');
    }

    /**
     * Tambah cabang baru.
     */
    public function createBranch(array $data): Branch
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        return Branch::create($data);
    }

    /**
     * Perbarui data cabang.
     */
    public function updateBranch(Branch $branch, array $data): Branch
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : $branch->is_active;

        $branch->update($data);

        return $branch;
    }

    /**
     * Ubah status aktif cabang.
     */
    public function toggleBranchStatus(Branch $branch): Branch
    {
        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        return $branch;
    }
}
