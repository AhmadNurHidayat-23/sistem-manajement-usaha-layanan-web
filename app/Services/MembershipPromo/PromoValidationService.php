<?php

namespace App\Services\MembershipPromo;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Models\Branch;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PromoValidationService
{
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
        $code = strtoupper(trim($code));
        $promo = Promotion::withCount('usages')->where('code', $code)->first();

        if (! $promo) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan.',
            ];
        }

        if (! in_array($promo->status, [PromotionStatus::Active, PromotionStatus::Approved], true)) {
            return [
                'valid' => false,
                'message' => 'Kode promo ini sedang tidak aktif.',
            ];
        }

        $today = Carbon::today();
        if ($today->lt($promo->start_date)) {
            return [
                'valid' => false,
                'message' => 'Promo ini belum mulai berlaku (mulai '.Carbon::parse($promo->start_date)->translatedFormat('d M Y').').',
            ];
        }

        if ($today->gt($promo->end_date)) {
            return [
                'valid' => false,
                'message' => 'Masa berlaku kode promo ini telah berakhir pada '.Carbon::parse($promo->end_date)->translatedFormat('d M Y').'.',
            ];
        }

        // Cek kesesuaian cabang
        if ($promo->branch_id !== null && $branchId !== null && $promo->branch_id !== (int) $branchId) {
            $branchName = $promo->branch?->name ?? 'Cabang Tertentu';

            return [
                'valid' => false,
                'message' => "Promo ini hanya berlaku khusus untuk {$branchName}.",
            ];
        }

        // Cek kuota
        if ($promo->quota !== null && $promo->usages_count >= $promo->quota) {
            return [
                'valid' => false,
                'message' => 'Kuota penggunaan kode promo ini sudah habis.',
            ];
        }

        // Hitung diskon
        $val = (float) $promo->discount_value;
        if ($promo->discount_type === DiscountType::Percentage) {
            $discount = round($amount * ($val / 100), 2);
        } else {
            $discount = $val;
        }

        $discount = min($amount, $discount);
        $finalTotal = max(0, $amount - $discount);

        return [
            'valid' => true,
            'promo' => $promo,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'message' => "Promo '{$promo->name}' berhasil diterapkan! Potongan: Rp ".number_format($discount, 0, ',', '.'),
        ];
    }

    /**
     * Catat penggunaan promo.
     */
    public function recordPromoUsage(Promotion $promotion, User $user, ?Transaction $transaction = null): PromotionUsage
    {
        return PromotionUsage::create([
            'promotion_id' => $promotion->id,
            'user_id' => $user->id,
            'transaction_id' => $transaction?->id,
            'used_at' => Carbon::now(),
        ]);
    }

    /**
     * Cabang yang dapat diakses.
     *
     * @return Collection<int, Branch>
     */
    public function getAccessibleBranches(User $user): Collection
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }

        if ($roleName === 'manager') {
            return $user->branches()->where('is_active', true)->orderBy('name')->get();
        }

        return new Collection;
    }

    /**
     * Statistik promo.
     *
     * @return array{total_promos: int, active_promos: int, total_usages: int, expired_promos: int}
     */
    public function getPromoStatistics(?int $branchId = null): array
    {
        $today = Carbon::today();
        $query = Promotion::query();

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            });
        }

        $activePromos = (clone $query)->where('status', PromotionStatus::Active)
            ->where('end_date', '>=', $today)
            ->count();

        $expiredPromos = (clone $query)->where(function ($q) use ($today) {
            $q->where('status', PromotionStatus::Expired)
                ->orWhere('end_date', '<', $today);
        })->count();

        $totalPromos = (clone $query)->count();
        $totalUsages = PromotionUsage::count();

        return [
            'total_promos' => $totalPromos,
            'active_promos' => $activePromos,
            'total_usages' => $totalUsages,
            'expired_promos' => $expiredPromos,
        ];
    }
}
