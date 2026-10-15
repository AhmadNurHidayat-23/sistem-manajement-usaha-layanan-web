<?php

namespace App\Services\MembershipPromo;

use App\Enums\PromotionStatus;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PromoService
{
    public function __construct(
        protected ?PromoValidationService $validationService = null,
    ) {
        $this->validationService = $this->validationService ?? new PromoValidationService;
    }

    /**
     * Daftar terpaginasi promotions.
     */
    public function getPromotions(?User $user = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Promotion::with('branch')
            ->withCount('usages');

        $roleName = $user?->role?->name;
        $isActiveOnly = ! empty($filters['active_only']) || ($roleName === 'customer');

        if ($roleName === 'manager' && ! $isActiveOnly) {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $managedBranchIds);
        }

        if ($isActiveOnly) {
            $today = Carbon::today();
            $query->whereIn('status', [PromotionStatus::Active, PromotionStatus::Approved])
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->where(function ($q) {
                    $q->whereNull('quota')
                        ->orWhereRaw('quota > (select count(*) from promotion_usages where promotion_usages.promotion_id = promotions.id)');
                });
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['status']) && ! $isActiveOnly) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['discount_type'])) {
            $query->where('discount_type', $filters['discount_type']);
        }

        if (! empty($filters['q'])) {
            $searchTerm = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', $searchTerm)
                    ->orWhere('name', 'like', $searchTerm);
            });
        }

        return $query->latest('start_date')->paginate($perPage);
    }

    /**
     * Promo aktif untuk landing page.
     *
     * @return Collection<int, Promotion>
     */
    public function getActivePromotions(?int $branchId = null, int $limit = 3): Collection
    {
        $today = Carbon::today();
        $query = Promotion::with('branch')
            ->whereIn('status', [PromotionStatus::Active, PromotionStatus::Approved])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($q) {
                $q->whereNull('quota')
                    ->orWhereRaw('quota > (select count(*) from promotion_usages where promotion_usages.promotion_id = promotions.id)');
            });

        if ($branchId !== null) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            });
        }

        return $query->latest('start_date')->take($limit)->get();
    }

    /**
     * Otorisasi kelola the given promotion.
     *
     * @throws AuthorizationException
     */
    public function authorizePromoManagement(User $user, ?Promotion $promotion = null, ?int $targetBranchId = null): void
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return;
        }

        if ($roleName === 'manager') {
            $managedBranchIds = $user->branches()->pluck('branches.id')->toArray();

            // When checking existing promo
            if ($promotion) {
                if (! $promotion->branch_id || ! in_array($promotion->branch_id, $managedBranchIds, true)) {
                    throw new AuthorizationException('Manager hanya dapat mengelola promo pada cabang yang dikelola.');
                }
            }

            // Cabang tujuan wajib ada dan harus cabang yang dikelola
            if ($targetBranchId === null || ! in_array($targetBranchId, $managedBranchIds, true)) {
                throw new AuthorizationException('Manager hanya dapat menetapkan promo untuk cabang yang dikelola.');
            }

            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin mengelola promo.');
    }

    /**
     * Tambah promo baru.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function createPromo(User $user, array $data): Promotion
    {
        $roleName = $user->role?->name;
        $branchId = ! empty($data['branch_id']) ? (int) $data['branch_id'] : null;

        if ($roleName === 'manager') {
            if ($branchId === null) {
                $branchId = (int) $user->branches()->first()?->id;
            }
            $status = PromotionStatus::Pending;
        } else {
            $status = $data['status'] ?? PromotionStatus::Active;
        }

        $this->authorizePromoManagement($user, null, $branchId);

        return Promotion::create([
            'branch_id' => $branchId,
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'discount_type' => $data['discount_type'],
            'discount_value' => (float) $data['discount_value'],
            'quota' => isset($data['quota']) && $data['quota'] !== '' && $data['quota'] !== null ? (int) $data['quota'] : null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $status,
        ]);
    }

    /**
     * Perbarui promo.
     *
     * @throws AuthorizationException
     */
    public function updatePromo(User $user, Promotion $promotion, array $data): Promotion
    {
        $branchId = array_key_exists('branch_id', $data) && ! empty($data['branch_id'])
            ? (int) $data['branch_id']
            : $promotion->branch_id;

        $this->authorizePromoManagement($user, $promotion, $branchId);

        if ($user->role?->name === 'manager') {
            // Manager cannot approve promo via update
            unset($data['status']);
        }

        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $promotion->update($data);

        return $promotion->fresh(['branch', 'usages']);
    }

    /**
     * Ubah status aktif promo.
     *
     * @throws AuthorizationException
     */
    public function togglePromoStatus(User $user, Promotion $promotion): Promotion
    {
        $this->authorizePromoManagement($user, $promotion);

        if ($promotion->status === PromotionStatus::Pending && $user->role?->name === 'manager') {
            throw new AuthorizationException('Promo yang menunggu persetujuan tidak dapat diubah statusnya oleh Manager.');
        }

        $newStatus = in_array($promotion->status, [PromotionStatus::Active, PromotionStatus::Approved], true)
            ? PromotionStatus::Inactive
            : PromotionStatus::Active;

        $promotion->update(['status' => $newStatus]);

        return $promotion;
    }

    /**
     * Setujui pengajuan promo (Owner Only).
     *
     * @throws AuthorizationException
     */
    public function approvePromo(User $user, Promotion $promotion): Promotion
    {
        if ($user->role?->name !== 'owner') {
            throw new AuthorizationException('Hanya Owner yang dapat menyetujui promo.');
        }

        $promotion->update(['status' => PromotionStatus::Approved]);

        return $promotion->fresh(['branch', 'usages']);
    }

    /**
     * Tolak pengajuan promo (Owner Only).
     *
     * @throws AuthorizationException
     */
    public function rejectPromo(User $user, Promotion $promotion): Promotion
    {
        if ($user->role?->name !== 'owner') {
            throw new AuthorizationException('Hanya Owner yang dapat menolak promo.');
        }

        $promotion->update(['status' => PromotionStatus::Rejected]);

        return $promotion->fresh(['branch', 'usages']);
    }

    /**
     * Validasi kode promo.
     *
     * @return array{
     *     valid: bool,
     *     message?: string,
     *     promo?: Promotion,
     *     discount?: float,
     *     final_total?: float
     * }
     */
    public function validatePromo(string $code, float $amount, ?int $branchId = null, ?User $user = null): array
    {
        return $this->validationService->validatePromo($code, $amount, $branchId, $user);
    }

    /**
     * Catat penggunaan promo.
     */
    public function recordPromoUsage(Promotion $promotion, User $user, ?Transaction $transaction = null): PromotionUsage
    {
        return $this->validationService->recordPromoUsage($promotion, $user, $transaction);
    }

    /**
     * Cabang yang dapat diakses.
     */
    public function getAccessibleBranches(User $user): Collection
    {
        return $this->validationService->getAccessibleBranches($user);
    }

    /**
     * Statistik promo.
     *
     * @return array{total_promos: int, active_promos: int, total_usages: int, expired_promos: int}
     */
    public function getPromoStatistics(?int $branchId = null): array
    {
        return $this->validationService->getPromoStatistics($branchId);
    }
}
