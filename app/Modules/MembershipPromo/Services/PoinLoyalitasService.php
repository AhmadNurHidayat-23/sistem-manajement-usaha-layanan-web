<?php

namespace App\Modules\MembershipPromo\Services;

use App\Enums\LoyaltyTransactionType;
use App\Enums\MembershipStatus;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Membership;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoinLoyalitasService
{
    /**
     * Tambah poin dari transaksi.
     */
    public function awardPointsFromTransaction(Transaction $transaction): ?LoyaltyTransaction
    {
        $customerUser = $transaction->booking?->user ?? $transaction->order?->user;

        if (! $customerUser) {
            return null;
        }

        $membership = $customerUser->membership;
        if (! $membership || $membership->status !== MembershipStatus::Active) {
            return null;
        }

        $pointsEarned = (int) floor(((float) $transaction->total) / 10000);
        if ($pointsEarned <= 0) {
            return null;
        }

        return DB::transaction(function () use ($membership, $transaction, $pointsEarned) {
            $loyaltyPoint = $membership->loyaltyPoint ?: LoyaltyPoint::firstOrCreate(
                ['membership_id' => $membership->id],
                ['total_points' => 0]
            );

            $loyaltyPoint->increment('total_points', $pointsEarned);

            return LoyaltyTransaction::create([
                'membership_id' => $membership->id,
                'transaction_id' => $transaction->id,
                'type' => LoyaltyTransactionType::Earned,
                'points' => $pointsEarned,
                'description' => "Poin dari transaksi POS #{$transaction->transaction_code}",
            ]);
        });
    }

    /**
     * Tukar poin reward.
     *
     * @throws ValidationException
     */
    public function redeemPoints(Membership $membership, int $points, string $rewardDescription): LoyaltyTransaction
    {
        $loyaltyPoint = $membership->loyaltyPoint;
        $currentPoints = $loyaltyPoint?->total_points ?? 0;

        if ($currentPoints < $points) {
            throw ValidationException::withMessages([
                'points' => "Poin tidak mencukupi. Saldo poin Anda saat ini adalah {$currentPoints} poin.",
            ]);
        }

        return DB::transaction(function () use ($membership, $loyaltyPoint, $points, $rewardDescription) {
            $loyaltyPoint->decrement('total_points', $points);

            return LoyaltyTransaction::create([
                'membership_id' => $membership->id,
                'type' => LoyaltyTransactionType::Redeemed,
                'points' => -$points,
                'description' => $rewardDescription,
            ]);
        });
    }
}
