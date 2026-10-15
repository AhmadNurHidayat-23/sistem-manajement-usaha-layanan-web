<?php

namespace App\Services\MembershipPromo;

use App\Enums\LoyaltyTransactionType;
use App\Enums\MembershipStatus;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Membership;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberService
{
    public function __construct(
        protected PoinLoyalitasService $pointService
    ) {}

    /**
     * Daftar terpaginasi members.
     *
     * @param  array{status?: ?string, q?: ?string}  $filters
     */
    public function getMembers(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Membership::with(['user', 'loyaltyPoint'])
            ->withCount('loyaltyTransactions');

        if ($user->role?->name === 'customer') {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $searchTerm = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('member_code', 'like', $searchTerm)
                    ->orWhereHas('user', function ($u) use ($searchTerm) {
                        $u->where('name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm);
                    });
            });
        }

        return $query->latest('joined_at')->paginate($perPage);
    }

    /**
     * Otorisasi akses view the given membership.
     *
     * @throws AuthorizationException
     */
    public function authorizeMemberAccess(User $user, Membership $membership): void
    {
        $roleName = $user->role?->name;

        if (in_array($roleName, ['owner', 'cashier'], true)) {
            return;
        }

        if ($roleName === 'customer' && $membership->user_id === $user->id) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki akses ke data member ini.');
    }

    /**
     * Daftarkan member baru.
     *
     * @param  array{user_id: int, member_code?: ?string}  $data
     */
    public function createMember(User $creator, array $data): Membership
    {
        return DB::transaction(function () use ($data) {
            $userId = $data['user_id'] ?? null;

            if (! $userId) {
                $customerRole = Role::where('name', 'customer')->first();
                $email = ! empty($data['email']) ? $data['email'] : 'cust_'.Str::random(8).'@customer.local';

                $existingUser = User::where(function ($q) use ($data, $email) {
                    if (! empty($data['phone'])) {
                        $q->where('phone', $data['phone']);
                    }
                    if (! empty($data['email'])) {
                        $q->orWhere('email', $email);
                    }
                })->first();

                if ($existingUser) {
                    if ($existingUser->membership) {
                        throw ValidationException::withMessages([
                            'phone' => 'Pelanggan dengan nomor telepon atau email ini sudah terdaftar sebagai member.',
                        ]);
                    }
                    $userId = $existingUser->id;
                } else {
                    $newUser = User::create([
                        'name' => $data['name'] ?? 'Pelanggan',
                        'phone' => $data['phone'] ?? null,
                        'email' => $email,
                        'password' => bcrypt(Str::random(16)),
                        'role_id' => $customerRole?->id,
                    ]);
                    $userId = $newUser->id;
                }
            }

            $memberCode = ! empty($data['member_code'])
                ? strtoupper($data['member_code'])
                : 'MBR-'.strtoupper(Str::random(6));

            $member = Membership::create([
                'user_id' => $userId,
                'member_code' => $memberCode,
                'joined_at' => Carbon::now(),
                'status' => MembershipStatus::Active,
            ]);

            // Bonus pendaftaran member
            $welcomePoints = 10;

            $loyaltyPoint = LoyaltyPoint::create([
                'membership_id' => $member->id,
                'total_points' => $welcomePoints,
            ]);

            LoyaltyTransaction::create([
                'membership_id' => $member->id,
                'type' => LoyaltyTransactionType::Earned,
                'points' => $welcomePoints,
                'description' => 'Bonus Pendaftaran Member Baru',
            ]);

            return $member->load(['user', 'loyaltyPoint']);
        });
    }

    /**
     * Daftar member mandiri.
     */
    public function registerSelfAsMember(User $customer): Membership
    {
        if ($customer->role?->name !== 'customer') {
            throw new AuthorizationException('Hanya akun pelanggan yang berhak mendaftar membership mandiri.');
        }

        if ($customer->membership) {
            return $customer->membership->load(['loyaltyPoint', 'loyaltyTransactions']);
        }

        return $this->createMember($customer, [
            'user_id' => $customer->id,
        ]);
    }

    /**
     * Detail member & riwayat poin.
     *
     * @throws AuthorizationException
     */
    public function getMemberDetails(User $user, Membership $membership): Membership
    {
        $this->authorizeMemberAccess($user, $membership);

        return $membership->load([
            'user',
            'loyaltyPoint',
            'loyaltyTransactions' => fn ($q) => $q->latest()->limit(50),
        ]);
    }

    /**
     * Perbarui status member.
     *
     * @param  array{status: MembershipStatus|string}  $data
     */
    public function updateMember(Membership $membership, array $data): Membership
    {
        $membership->update($data);

        return $membership->fresh(['user', 'loyaltyPoint']);
    }

    /**
     * Tambah poin dari transaksi.
     */
    public function awardPointsFromTransaction(Transaction $transaction): ?LoyaltyTransaction
    {
        return $this->pointService->awardPointsFromTransaction($transaction);
    }

    /**
     * Tukar poin reward.
     *
     * @throws AuthorizationException|ValidationException
     */
    public function redeemPoints(User $user, Membership $membership, int $points, string $rewardDescription): LoyaltyTransaction
    {
        $this->authorizeMemberAccess($user, $membership);

        return $this->pointService->redeemPoints($membership, $points, $rewardDescription);
    }

    /**
     * Paket langganan member.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSubscriptionPackages(): array
    {
        return [
            [
                'name' => 'Member Reguler (Bronze)', 'level' => 'Bronze', 'badge' => 'neutral',
                'min_points' => 0, 'popular' => false,
                'description' => 'Keanggotaan dasar untuk setiap pelanggan terdaftar.',
                'features' => ['1 Poin per transaksi Rp 10.000', 'Riwayat transaksi online', 'Penukaran poin potongan harga', 'Akses promo member'],
            ],
            [
                'name' => 'Paket Berlangganan Silver', 'level' => 'Silver', 'badge' => 'primary',
                'min_points' => 100, 'popular' => true,
                'description' => 'Paket ideal untuk pelanggan aktif yang menginginkan benefit lebih.',
                'features' => ['Multiplikasi Poin 1.25x', 'Diskon member 5%', 'Akses promo eksklusif berkala', 'Prioritas penawaran promo'],
            ],
            [
                'name' => 'Paket Berlangganan Gold VIP', 'level' => 'Gold', 'badge' => 'warning',
                'min_points' => 250, 'popular' => false,
                'description' => 'Keanggotaan premium dengan benefit diskon dan reward lebih tinggi.',
                'features' => ['Multiplikasi Poin 1.5x', 'Diskon member 10%', 'Benefit promo premium pilihan', 'Reward berkala'],
            ],
            [
                'name' => 'Paket Platinum Sultan VIP', 'level' => 'Platinum', 'badge' => 'success',
                'min_points' => 500, 'popular' => false,
                'description' => 'Tingkat keanggotaan tertinggi dengan multiplier poin dan diskon maksimal.',
                'features' => ['Multiplikasi Poin 2.0x transaksi', 'Diskon member 15%', 'Benefit reward tertinggi', 'Akses promosi eksklusif Platinum'],
            ],
        ];
    }

    /**
     * Statistik member.
     *
     * @return array{total_members: int, active_members: int, total_points: int, points_redeemed: int}
     */
    public function getMemberStatistics(): array
    {
        $totalMembers = Membership::count();
        $activeMembers = Membership::where('status', MembershipStatus::Active)->count();
        $totalPoints = (int) LoyaltyPoint::sum('total_points');
        $pointsRedeemed = (int) abs(LoyaltyTransaction::where('type', LoyaltyTransactionType::Redeemed)->sum('points'));

        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'total_points' => $totalPoints,
            'points_redeemed' => $pointsRedeemed,
        ];
    }
}
