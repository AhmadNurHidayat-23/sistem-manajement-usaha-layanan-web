<?php

namespace App\Modules\Katalog\Services;

use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UlasanService
{
    /**
     * Daftar ulasan terpaginasi.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getUlasanList(User $viewer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Ulasan::query()
            ->with(['user', 'service', 'employee', 'transaction'])
            ->latest();

        if (! empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (! empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Semua ulasan layanan.
     */
    public function getUlasanForService(int $serviceId, int $perPage = 10): LengthAwarePaginator
    {
        return Ulasan::query()
            ->with(['user', 'employee'])
            ->where('service_id', $serviceId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Ulasan untuk landing page.
     *
     * @return Collection<int, Ulasan>
     */
    public function getLandingReviews(?int $branchId = null, int $limit = 3): Collection
    {
        $query = Ulasan::query()
            ->with(['user', 'service.branch', 'transaction'])
            ->latest();

        if ($branchId !== null) {
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('service', fn ($sub) => $sub->where('branch_id', $branchId))
                    ->orWhereHas('transaction', fn ($sub) => $sub->where('branch_id', $branchId));
            });
        }

        return $query->take($limit)->get();
    }

    /**
     * Statistik ulasan layanan.
     *
     * @return array{average: float, count: int}
     */
    public function getServiceRatingStats(int $serviceId): array
    {
        $result = Ulasan::where('service_id', $serviceId)
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        return [
            'average' => round((float) ($result->average ?? 0), 1),
            'count' => (int) ($result->count ?? 0),
        ];
    }

    /**
     * Tentukan transaksi untuk ulasan.
     * Validasi kepemilikan transaksi lunas.
     *
     * @throws AuthorizationException
     */
    public function resolveCustomerTransaction(User $customer, int $transactionId): Transaction
    {
        $transaction = Transaction::with(['ulasan', 'booking', 'order'])
            ->where('id', $transactionId)
            ->firstOrFail();

        $isOwner = ($transaction->booking?->user_id === $customer->id)
            || ($transaction->order?->user_id === $customer->id);

        if (! $isOwner) {
            throw new AuthorizationException('Anda tidak memiliki akses ke transaksi ini.');
        }

        if ($transaction->status->value !== 'paid') {
            throw new AuthorizationException('Ulasan hanya dapat diberikan setelah transaksi selesai (Paid).');
        }

        return $transaction;
    }

    /**
     * Create a new ulasan.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function createUlasan(User $customer, array $data): Ulasan
    {
        $transaction = $this->resolveCustomerTransaction($customer, (int) $data['transaction_id']);

        if ($transaction->ulasan !== null) {
            throw new AuthorizationException('Transaksi ini sudah memiliki ulasan.');
        }

        return Ulasan::create([
            'user_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'service_id' => $data['service_id'],
            'employee_id' => $data['employee_id'] ?? null,
            'rating' => $data['rating'],
            'komentar' => $data['komentar'] ?? null,
        ]);
    }

    /**
     * Update an existing ulasan (owner only).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function updateUlasan(User $customer, Ulasan $ulasan, array $data): Ulasan
    {
        if ($ulasan->user_id !== $customer->id) {
            throw new AuthorizationException('Anda hanya dapat mengubah ulasan milik Anda sendiri.');
        }

        $ulasan->update([
            'rating' => $data['rating'],
            'komentar' => $data['komentar'] ?? null,
        ]);

        return $ulasan->fresh();
    }
}
