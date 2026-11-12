<?php

namespace App\Services\LaporanKeuangan;

use Carbon\Carbon;

class LaporanPeriodResolver
{
    /**
     * Resolusi rentang tanggal dan kriteria filter laporan.
     *
     * @param  array<string, mixed>  $customFilters
     * @return array{
     *     start_date: ?Carbon,
     *     end_date: ?Carbon,
     *     period: string,
     *     is_custom: bool,
     *     custom_filters: array{day: ?string, date: ?int, month: ?int, year: ?int},
     *     formatted_label: string
     * }
     */
    public function resolve(?string $period = null, ?string $startDate = null, ?string $endDate = null, array $customFilters = []): array
    {
        $now = Carbon::now();

        $day = ! empty($customFilters['day']) ? strtolower(trim((string) $customFilters['day'])) : null;
        $date = ! empty($customFilters['date']) ? (int) $customFilters['date'] : null;
        $month = ! empty($customFilters['month']) ? (int) $customFilters['month'] : null;
        $year = ! empty($customFilters['year']) ? (int) $customFilters['year'] : null;

        $hasCustomFields = ($day !== null) || ($date !== null) || ($month !== null) || ($year !== null);
        $isExplicitCustom = ($period === 'custom') || $hasCustomFields || (empty($period) && $startDate && $endDate);

        $parsedCustomFilters = [
            'day' => $day,
            'date' => $date,
            'month' => $month,
            'year' => $year,
        ];

        // 1. Mode kustom eksplisit (baik melalui dropdown 4 opsi atau rentang tanggal legacy)
        if ($isExplicitCustom) {
            $formattedLabel = $this->formatCustomLabel($parsedCustomFilters);

            $parsedStart = null;
            $parsedEnd = null;

            if ($year !== null) {
                if ($month !== null) {
                    $parsedStart = Carbon::create($year, $month, 1)->startOfMonth();
                    $parsedEnd = Carbon::create($year, $month, 1)->endOfMonth();
                } else {
                    $parsedStart = Carbon::create($year, 1, 1)->startOfYear();
                    $parsedEnd = Carbon::create($year, 12, 31)->endOfYear();
                }
            } elseif ($startDate && $endDate) {
                $parsedStart = Carbon::parse($startDate)->startOfDay();
                $parsedEnd = Carbon::parse($endDate)->endOfDay();

                if ($parsedStart->greaterThan($parsedEnd)) {
                    $parsedEnd = $parsedStart->copy()->endOfDay();
                }

                $formattedLabel = $parsedStart->translatedFormat('d M Y').' – '.$parsedEnd->translatedFormat('d M Y');
            }

            return [
                'start_date' => $parsedStart,
                'end_date' => $parsedEnd,
                'period' => 'custom',
                'is_custom' => true,
                'custom_filters' => $parsedCustomFilters,
                'formatted_label' => $formattedLabel,
            ];
        }

        // 2. Preset periode bawaan
        return match ($period) {
            'today' => [
                'start_date' => $now->copy()->startOfDay(),
                'end_date' => $now->copy()->endOfDay(),
                'period' => 'today',
                'is_custom' => false,
                'custom_filters' => $parsedCustomFilters,
                'formatted_label' => $now->translatedFormat('d M Y'),
            ],
            'this_week', 'week' => [
                'start_date' => $now->copy()->startOfWeek(),
                'end_date' => $now->copy()->endOfWeek(),
                'period' => 'this_week',
                'is_custom' => false,
                'custom_filters' => $parsedCustomFilters,
                'formatted_label' => $now->copy()->startOfWeek()->translatedFormat('d M').' – '.$now->copy()->endOfWeek()->translatedFormat('d M Y'),
            ],
            'this_year', 'year' => [
                'start_date' => $now->copy()->startOfYear(),
                'end_date' => $now->copy()->endOfYear(),
                'period' => 'this_year',
                'is_custom' => false,
                'custom_filters' => $parsedCustomFilters,
                'formatted_label' => 'Tahun '.$now->format('Y'),
            ],
            default => [
                'start_date' => $now->copy()->startOfMonth(),
                'end_date' => $now->copy()->endOfMonth(),
                'period' => 'this_month',
                'is_custom' => false,
                'custom_filters' => $parsedCustomFilters,
                'formatted_label' => $now->translatedFormat('F Y'),
            ],
        };
    }

    /**
     * Format label deskriptif bahasa Indonesia berdasarkan kombinasi filter kustom.
     *
     * @param  array{day: ?string, date: ?int, month: ?int, year: ?int}  $filters
     */
    public function formatCustomLabel(array $filters): string
    {
        $day = ! empty($filters['day']) ? strtolower((string) $filters['day']) : null;
        $date = ! empty($filters['date']) ? (int) $filters['date'] : null;
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $year = ! empty($filters['year']) ? (int) $filters['year'] : null;

        $dayNames = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu',
            'minggu' => 'Minggu',
            '1' => 'Senin',
            '2' => 'Selasa',
            '3' => 'Rabu',
            '4' => 'Kamis',
            '5' => 'Jumat',
            '6' => 'Sabtu',
            '7' => 'Minggu',
        ];

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $hasDay = $day && isset($dayNames[$day]);
        $hasDate = $date && $date >= 1 && $date <= 31;
        $hasMonth = $month && isset($monthNames[$month]);
        $hasYear = $year && $year > 1900;

        // 1. Keempat filter terisi: misal "Senin, 12 November 2026"
        if ($hasDay && $hasDate && $hasMonth && $hasYear) {
            return "{$dayNames[$day]}, {$date} {$monthNames[$month]} {$year}";
        }

        // 2. Tanggal, Bulan, Tahun: "12 November 2026"
        if (! $hasDay && $hasDate && $hasMonth && $hasYear) {
            return "{$date} {$monthNames[$month]} {$year}";
        }

        // 3. Hari, Bulan, Tahun: "Hari Senin, November 2026"
        if ($hasDay && ! $hasDate && $hasMonth && $hasYear) {
            return "Hari {$dayNames[$day]}, {$monthNames[$month]} {$year}";
        }

        // 4. Hari, Tanggal, Bulan: "Hari Senin, 12 November (Semua Tahun)"
        if ($hasDay && $hasDate && $hasMonth && ! $hasYear) {
            return "Hari {$dayNames[$day]}, {$date} {$monthNames[$month]} (Semua Tahun)";
        }

        // 5. Tanggal & Bulan: "Tanggal 12 November"
        if (! $hasDay && $hasDate && $hasMonth && ! $hasYear) {
            return "Tanggal {$date} {$monthNames[$month]} (Semua Tahun)";
        }

        // 6. Bulan & Tahun: "November 2026"
        if (! $hasDay && ! $hasDate && $hasMonth && $hasYear) {
            return "{$monthNames[$month]} {$year}";
        }

        // 7. Hari & Tahun: "Hari Senin Tahun 2026"
        if ($hasDay && ! $hasDate && ! $hasMonth && $hasYear) {
            return "Hari {$dayNames[$day]} Tahun {$year}";
        }

        // 8. Tanggal & Tahun: "Tanggal 12 di Tahun 2026"
        if (! $hasDay && $hasDate && ! $hasMonth && $hasYear) {
            return "Tanggal {$date} di Tahun {$year}";
        }

        // 9. Hari & Bulan: "Hari Senin di Bulan November"
        if ($hasDay && ! $hasDate && $hasMonth && ! $hasYear) {
            return "Hari {$dayNames[$day]} di Bulan {$monthNames[$month]}";
        }

        // 10. Hari saja: "Hari Senin (Semua Periode)"
        if ($hasDay && ! $hasDate && ! $hasMonth && ! $hasYear) {
            return "Hari {$dayNames[$day]} (Semua Periode)";
        }

        // 11. Tanggal saja: "Tanggal 12 (Semua Periode)"
        if (! $hasDay && $hasDate && ! $hasMonth && ! $hasYear) {
            return "Tanggal {$date} (Semua Periode)";
        }

        // 12. Bulan saja: "Bulan November (Semua Tahun)"
        if (! $hasDay && ! $hasDate && $hasMonth && ! $hasYear) {
            return "Bulan {$monthNames[$month]} (Semua Tahun)";
        }

        // 13. Tahun saja: "Tahun 2026"
        if (! $hasDay && ! $hasDate && ! $hasMonth && $hasYear) {
            return "Tahun {$year}";
        }

        // 14. Hari & Tanggal: "Hari Senin, Tanggal 12"
        if ($hasDay && $hasDate && ! $hasMonth && ! $hasYear) {
            return "Hari {$dayNames[$day]}, Tanggal {$date}";
        }

        // 15. Semua kosong: "Semua Data Transaksi"
        return 'Semua Data Transaksi';
    }
}
