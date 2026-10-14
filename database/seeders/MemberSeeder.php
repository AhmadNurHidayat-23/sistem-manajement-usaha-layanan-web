<?php

namespace Database\Seeders;

use App\Enums\LoyaltyTransactionType;
use App\Enums\MembershipStatus;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Membership;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Jalankan seeder program membership dan poin loyalitas pelanggan.
     */
    public function run(): void
    {
        $customer = User::whereIn('email', ['pelanggan@simaul.com', 'customer@example.com'])->firstOrFail();
        $transaction1 = Transaction::where('transaction_code', 'TRX-DEMO-001')->firstOrFail();

        // 1. Data Member
        $member = Membership::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'member_code' => 'MBR-DEMO01',
                'joined_at' => Carbon::now()->subMonths(1),
                'status' => MembershipStatus::Active,
            ]
        );

        // 2. Saldo Poin Loyalitas
        LoyaltyPoint::firstOrCreate(
            ['membership_id' => $member->id],
            [
                'total_points' => 75,
            ]
        );

        // 3. Riwayat Transaksi Poin Selamat Datang
        LoyaltyTransaction::firstOrCreate(
            [
                'membership_id' => $member->id,
                'description' => 'Bonus Pendaftaran Member Baru',
            ],
            [
                'type' => LoyaltyTransactionType::Earned,
                'points' => 10,
            ]
        );

        // 4. Riwayat Perolehan Poin dari Transaksi
        LoyaltyTransaction::firstOrCreate(
            [
                'membership_id' => $member->id,
                'transaction_id' => $transaction1->id,
            ],
            [
                'type' => LoyaltyTransactionType::Earned,
                'points' => 22,
                'description' => "Poin dari transaksi POS #{$transaction1->transaction_code}",
            ]
        );
    }
}
