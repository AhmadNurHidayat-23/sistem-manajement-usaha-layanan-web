<?php

namespace App\Modules\LaporanKeuangan\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class TrenLaporanService
{
    /**
     * Hitung tren penjualan harian atau bulanan untuk grafik.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return array{data: array<int, array<string, mixed>>, max_omzet: float, max_transactions: int}
     */
    public function buildTrendData(Collection $transactions, Carbon $startDate, Carbon $endDate): array
    {
        $diffInDays = $startDate->diffInDays($endDate);

        // Kelompokkan transaksi
        $grouped = $transactions->groupBy(function ($trx) {
            return Carbon::parse($trx->transaction_date)->format('Y-m-d');
        });

        $data = [];
        $maxOmzet = 0.0;
        $maxTransactions = 0;

        if ($diffInDays <= 60) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $dayTrxs = $grouped->get($dateKey, collect());

                $dayOmzet = (float) $dayTrxs->sum('total');
                $dayCount = $dayTrxs->count();

                if ($dayOmzet > $maxOmzet) {
                    $maxOmzet = $dayOmzet;
                }
                if ($dayCount > $maxTransactions) {
                    $maxTransactions = $dayCount;
                }

                $data[] = [
                    'date' => $dateKey,
                    'day_name' => $date->translatedFormat('D'),
                    'label' => $date->translatedFormat('d M'),
                    'omzet' => $dayOmzet,
                    'transactions_count' => $dayCount,
                ];
            }
        } else {
            $monthlyGrouped = $transactions->groupBy(function ($trx) {
                return Carbon::parse($trx->transaction_date)->format('Y-m');
            });

            $currentMonth = $startDate->copy()->startOfMonth();
            while ($currentMonth->lte($endDate)) {
                $monthKey = $currentMonth->format('Y-m');
                $monthTrxs = $monthlyGrouped->get($monthKey, collect());

                $mOmzet = (float) $monthTrxs->sum('total');
                $mCount = $monthTrxs->count();

                if ($mOmzet > $maxOmzet) {
                    $maxOmzet = $mOmzet;
                }
                if ($mCount > $maxTransactions) {
                    $maxTransactions = $mCount;
                }

                $data[] = [
                    'date' => $monthKey,
                    'day_name' => $currentMonth->translatedFormat('F'),
                    'label' => $currentMonth->translatedFormat('M Y'),
                    'omzet' => $mOmzet,
                    'transactions_count' => $mCount,
                ];

                $currentMonth->addMonth();
            }
        }

        // Persentase grafik
        $safeMaxOmzet = max(1, $maxOmzet);
        foreach ($data as &$point) {
            $point['percentage'] = min(100, round(($point['omzet'] / $safeMaxOmzet) * 100));
        }

        return [
            'data' => $data,
            'max_omzet' => $maxOmzet,
            'max_transactions' => $maxTransactions,
        ];
    }
}
