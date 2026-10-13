<?php

namespace App\Services;

use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AktivitasService
{
    /**
     * Record a single activity log entry.
     *
     * @param  array<string, mixed>  $extra
     */
    public function catat(
        ?User $user,
        string $aktivitas,
        string $modul,
        ?string $keterangan = null,
        ?string $ipAddress = null,
    ): Aktivitas {
        return Aktivitas::create([
            'user_id' => $user?->id,
            'aktivitas' => $aktivitas,
            'modul' => $modul,
            'keterangan' => $keterangan,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Catat aktivitas dari request.
     */
    public function catatDariRequest(
        Request $request,
        string $aktivitas,
        string $modul,
        ?string $keterangan = null,
    ): Aktivitas {
        return $this->catat(
            user: $request->user(),
            aktivitas: $aktivitas,
            modul: $modul,
            keterangan: $keterangan,
            ipAddress: $request->ip(),
        );
    }

    /**
     * Get paginated activity logs.
     * Filter riwayat aktivitas.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getLogs(User $viewer, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Aktivitas::query()
            ->with('user')
            ->latest('created_at');

        $roleName = $viewer->role?->name;

        if ($roleName === 'manager') {
            // Log cabang manager
            $branchUserIds = User::whereHas('branches', function (Builder $q) use ($viewer) {
                $q->whereIn('branches.id', $viewer->branches()->pluck('branches.id'));
            })->pluck('id');

            $query->where(function (Builder $q) use ($branchUserIds, $viewer) {
                $q->whereIn('user_id', $branchUserIds)
                    ->orWhere('user_id', $viewer->id);
            });
        }

        if (! empty($filters['modul'])) {
            $query->where('modul', $filters['modul']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Daftar modul untuk filter.
     *
     * @return Collection<int, string>
     */
    public function getModulList(): Collection
    {
        return Aktivitas::distinct()->orderBy('modul')->pluck('modul');
    }
}
