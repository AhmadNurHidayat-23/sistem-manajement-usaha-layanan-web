<?php

namespace App\Services\LaporanKeuangan;

use Carbon\Carbon;

class LaporanPeriodResolver
{
    /**
     * Resolusi rentang tanggal laporan.
     *
     * @return array{start_date: Carbon, end_date: Carbon, period: string}
     */
    public function resolve(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $now = Carbon::now();

        // 1. Mode kustom eksplisit atau parameter tanggal terisi tanpa preset
        if ($period === 'custom' || (empty($period) && $startDate && $endDate)) {
            $parsedStart = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth();
            $parsedEnd = $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfMonth();

            if ($parsedStart->greaterThan($parsedEnd)) {
                $parsedEnd = $parsedStart->copy()->endOfDay();
            }

            return [
                'start_date' => $parsedStart,
                'end_date' => $parsedEnd,
                'period' => 'custom',
            ];
        }

        // 2. Preset periode
        return match ($period) {
            'today' => [
                'start_date' => $now->copy()->startOfDay(),
                'end_date' => $now->copy()->endOfDay(),
                'period' => 'today',
            ],
            'this_week', 'week' => [
                'start_date' => $now->copy()->startOfWeek(),
                'end_date' => $now->copy()->endOfWeek(),
                'period' => 'this_week',
            ],
            'this_year', 'year' => [
                'start_date' => $now->copy()->startOfYear(),
                'end_date' => $now->copy()->endOfYear(),
                'period' => 'this_year',
            ],
            default => [
                'start_date' => $now->copy()->startOfMonth(),
                'end_date' => $now->copy()->endOfMonth(),
                'period' => 'this_month',
            ],
        };
    }
}
