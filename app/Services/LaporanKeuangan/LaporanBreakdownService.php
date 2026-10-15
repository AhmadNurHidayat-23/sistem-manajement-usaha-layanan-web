<?php

namespace App\Services\LaporanKeuangan;

use App\Models\PromotionUsage;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanBreakdownService
{
    /**
     * Hitung rincian metode pembayaran.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return array<int, array<string, mixed>>
     */
    public function buildPaymentMethodsBreakdown(Collection $transactions, float $totalOmzet): array
    {
        $allPayments = $transactions->flatMap(fn ($t) => $t->payments);
        $grouped = $allPayments->groupBy('payment_method');
        $result = [];

        $methodNames = [
            'cash' => 'Tunai (Cash)',
            'qris' => 'QRIS Digital',
            'transfer' => 'Transfer Bank',
            'card' => 'Kartu Debit / Kredit',
            'other' => 'Lainnya',
        ];

        foreach ($grouped as $method => $payments) {
            $amount = (float) $payments->sum('amount');
            $count = $payments->count();
            $percentage = $totalOmzet > 0 ? round(($amount / $totalOmzet) * 100, 1) : 0;

            $result[] = [
                'payment_method' => $method,
                'method_label' => $methodNames[$method] ?? ucfirst((string) $method),
                'count' => $count,
                'total_amount' => $amount,
                'percentage' => $percentage,
            ];
        }

        usort($result, fn ($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        return $result;
    }

    /**
     * Hitung rincian penggunaan voucher promosi.
     *
     * @return array{total_usages: int, total_discount: float, list: array<int, array<string, mixed>>}
     */
    public function buildPromotionsBreakdown(Carbon $startDate, Carbon $endDate, ?int $branchId): array
    {
        $query = PromotionUsage::with(['promotion', 'transaction'])
            ->whereBetween('used_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->whereHas('transaction', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $usages = $query->get();
        $totalUsages = $usages->count();

        $grouped = $usages->groupBy('promotion_id');
        $list = [];
        $totalDiscount = 0.0;

        foreach ($grouped as $promoId => $promoUsages) {
            $promo = $promoUsages->first()?->promotion;
            $usagesCount = $promoUsages->count();

            $discountGiven = (float) $promoUsages->sum(function ($u) {
                return (float) ($u->transaction?->discount ?? 0);
            });

            $totalDiscount += $discountGiven;

            $list[] = [
                'promotion_id' => $promoId,
                'code' => $promo?->code ?? 'VOUCHER',
                'name' => $promo?->name ?? 'Promo Diskon',
                'discount_type' => $promo?->discount_type?->value ?? 'fixed',
                'discount_value' => (float) ($promo?->discount_value ?? 0),
                'usage_count' => $usagesCount,
                'total_discount' => $discountGiven,
            ];
        }

        usort($list, fn ($a, $b) => $b['usage_count'] <=> $a['usage_count']);

        return [
            'total_usages' => $totalUsages,
            'total_discount' => $totalDiscount,
            'list' => $list,
        ];
    }

    /**
     * Hitung rincian pendapatan per layanan.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @return array<int, array<string, mixed>>
     */
    public function buildServicesBreakdown(Collection $transactions, float $totalOmzet): array
    {
        $allDetails = $transactions->flatMap(fn ($t) => $t->details);
        $grouped = $allDetails->groupBy('service_id');
        $result = [];

        foreach ($grouped as $serviceId => $details) {
            $service = $details->first()?->service;
            $qty = (float) $details->sum('qty');
            $revenue = (float) $details->sum('subtotal');
            $percentage = $totalOmzet > 0 ? round(($revenue / $totalOmzet) * 100, 1) : 0;

            $result[] = [
                'service_id' => $serviceId,
                'service_name' => $service?->name ?? 'Layanan Servis',
                'category_name' => $service?->category?->name ?? 'Umum',
                'price' => (float) ($service?->price ?? 0),
                'total_qty' => $qty,
                'total_revenue' => $revenue,
                'percentage' => $percentage,
            ];
        }

        usort($result, fn ($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        return $result;
    }
}
