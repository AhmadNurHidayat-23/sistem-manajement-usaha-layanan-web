<?php

namespace App\Services\KasirPOS;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TransaksiQueryService
{
    public function __construct(
        protected KasirService $kasirService,
    ) {}

    /**
     * Daftar transaksi terpaginasi (Hanya menampilkan transaksi yang telah lunas).
     *
     * @param  array{
     *     branch_id?: ?int,
     *     status?: ?string,
     *     payment_method?: ?string,
     *     start_date?: ?string,
     *     end_date?: ?string,
     *     q?: ?string
     * }  $filters
     */
    public function getTransactionsForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $roleName = $user->role?->name;
        $query = Transaction::with(['branch', 'cashier', 'payments', 'booking.user', 'order.user', 'details.service'])
            ->where('status', StatusTransaksi::Paid);

        if ($roleName === 'owner') {
            if (! empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }
        } elseif ($roleName === 'manager') {
            $accessibleBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $query->whereIn('branch_id', $accessibleBranchIds);

            if (! empty($filters['branch_id']) && in_array((int) $filters['branch_id'], $accessibleBranchIds, true)) {
                $query->where('branch_id', $filters['branch_id']);
            }
        } else {
            // Cashier
            $cashierBranch = $this->kasirService->getActiveBranchForUser($user);
            $branchId = $cashierBranch?->id ?? 0;
            $query->where('branch_id', $branchId);
        }

        if (! empty($filters['payment_method'])) {
            $method = $filters['payment_method'];
            $query->where(function ($q) use ($method) {
                $q->whereHas('payments', function ($p) use ($method) {
                    $p->where(function ($sq) use ($method) {
                        if ($method === 'e_wallet') {
                            $sq->whereIn('payment_method', ['e_wallet', 'e-wallet']);
                        } else {
                            $sq->where('payment_method', $method);
                        }
                    })->whereIn('status', [StatusPembayaran::Paid, StatusPembayaran::Completed]);
                });

                if ($method === 'cash') {
                    $q->orDoesntHave('payments');
                }
            });
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereBetween('transaction_date', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif (! empty($filters['start_date'])) {
            $query->where('transaction_date', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        } elseif (! empty($filters['end_date'])) {
            $query->where('transaction_date', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        if (! empty($filters['q'])) {
            $keyword = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('transaction_code', 'like', $keyword)
                    ->orWhereHas('cashier', fn ($c) => $c->where('name', 'like', $keyword))
                    ->orWhereHas('booking.user', fn ($u) => $u->where('name', 'like', $keyword))
                    ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', $keyword));
            });
        }

        return $query->latest('transaction_date')->paginate($perPage);
    }

    /**
     * Detail transaksi lengkap.
     *
     * @throws AuthorizationException
     */
    public function getTransactionDetails(User $user, Transaction $transaction): Transaction
    {
        $roleName = $user->role?->name;

        if ($roleName === 'owner') {
            // unrestricted
        } elseif ($roleName === 'manager') {
            $accessibleBranchIds = $user->branches()->pluck('branches.id')->toArray();
            if (! in_array($transaction->branch_id, $accessibleBranchIds, true)) {
                throw new AuthorizationException('Anda tidak memiliki akses ke transaksi cabang ini.');
            }
        } else {
            // Cashier
            $cashierBranch = $this->kasirService->getActiveBranchForUser($user);
            if (! $cashierBranch || $cashierBranch->id !== $transaction->branch_id) {
                throw new AuthorizationException('Anda hanya dapat melihat transaksi pada cabang Anda.');
            }
        }

        return $transaction->load(['branch', 'cashier', 'details.service.category', 'payments', 'booking.user', 'order.user']);
    }
}
