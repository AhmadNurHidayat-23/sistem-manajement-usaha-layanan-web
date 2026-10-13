<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransaksiPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat detail transaksi.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            return true;
        }

        if (in_array($roleName, ['manager', 'cashier'], true)) {
            return $user->branches()->where('branches.id', $transaction->branch_id)->exists();
        }

        return ($transaction->booking?->user_id === $user->id)
            || ($transaction->order?->user_id === $user->id);
    }

    /**
     * Tentukan apakah pengguna dapat mencetak struk transaksi.
     */
    public function receipt(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction);
    }
}
