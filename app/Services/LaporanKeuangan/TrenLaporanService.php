<?php

namespace App\Services\LaporanKeuangan;

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
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, max_omzet: float, max_transactions: int}
     */
    public function buildTrendData(Collection $transactions, ?Carbon $startDate = null, ?Carbon $endDate = null, array $filters = []): array
    {
        if ($transactions->isEmpty()) {
            return [
                'data' => [],
                'max_omzet' => 0.0,
                'max_transactions' => 0,
            ];
        }

        $period = $filters['period'] ?? null;
        $isPreset = in_array($period, ['today', 'this_week', 'week', 'this_month', 'month', 'this_year', 'year'], true);

        $data = [];
        $maxOmzet = 0.0;
        $maxTransactions = 0;

        // 1. Preset periode dengan rentang awal-akhir yang terdefinisi
        if ($isPreset && $startDate !== null && $endDate !== null) {
            $diffInDays = $startDate->diffInDays($endDate);

            $grouped = $transactions->groupBy(function ($trx) {
                return Carbon::parse($trx->transaction_date)->format('Y-m-d');
            });

            if ($diffInDays <= 60) {
                $calendarPeriod = CarbonPeriod::create($startDate, $endDate);
                foreach ($calendarPeriod as $date) {
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
        } else {
            // 2. Mode Kustom (Hari, Tanggal, Bulan, Tahun, atau seluruh transaksi)
            // Kelompokkan data asli secara kronologis
            $groupedByDate = $transactions->groupBy(function ($trx) {
                return Carbon::parse($trx->transaction_date)->format('Y-m-d');
            })->sortKeys();

            if ($groupedByDate->count() <= 31) {
                foreach ($groupedByDate as $dateKey => $dayTrxs) {
                    $dateObj = Carbon::parse($dateKey);
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
                        'day_name' => $dateObj->translatedFormat('l'),
                        'label' => $dateObj->translatedFormat('d M'),
                        'omzet' => $dayOmzet,
                        'transactions_count' => $dayCount,
                    ];
                }
            } else {
                $groupedByMonth = $transactions->groupBy(function ($trx) {
                    return Carbon::parse($trx->transaction_date)->format('Y-m');
                })->sortKeys();

                foreach ($groupedByMonth as $monthKey => $monthTrxs) {
                    $monthObj = Carbon::parse($monthKey.'-01');
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
                        'day_name' => $monthObj->translatedFormat('F'),
                        'label' => $monthObj->translatedFormat('M Y'),
                        'omzet' => $mOmzet,
                        'transactions_count' => $mCount,
                    ];
                }
            }
        }

        // Persentase grafik aman
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
