<?php

namespace App\Services\KasirPOS;

use App\Enums\BookingStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class TransaksiSourceService
{
    public function getEligibleBookings(int $branchId): Collection
    {
        $branch = Branch::find($branchId);
        if ($branch && $branch->service_mode === 'drop_off') {
            return new Collection;
        }

        return Booking::with(['service', 'user'])
            ->where('branch_id', $branchId)
            ->whereNotIn('status', [BookingStatus::Cancelled])
            ->whereDoesntHave('transaction', fn ($q) => $q->where('status', StatusTransaksi::Paid))
            ->latest('booking_date')
            ->limit(30)
            ->get();
    }

    public function getEligibleOrders(int $branchId): Collection
    {
        $branch = Branch::find($branchId);
        if ($branch && $branch->service_mode === 'appointment') {
            return new Collection;
        }

        return Order::with(['service', 'user'])
            ->where('branch_id', $branchId)
            ->whereNotIn('status', [StatusPesanan::Cancelled])
            ->whereDoesntHave('transaction', fn ($q) => $q->where('status', StatusTransaksi::Paid))
            ->whereHas('service', fn ($q) => $q->where('service_type', '!=', 'appointment')->orWhereNull('service_type'))
            ->latest()
            ->limit(30)
            ->get();
    }

    /**
     * Daftar tagihan yang perlu ditangani kasir (Appointment & Drop-off).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getPendingPayments(Branch $branch, string $typeFilter = 'all'): \Illuminate\Support\Collection
    {
        $items = collect();
        $mode = $branch->service_mode ?? 'both';

        $includeAppointments = $mode === 'appointment' || ($mode === 'both' && $typeFilter !== 'drop_off');
        $includeDropOff = $mode === 'drop_off' || ($mode === 'both' && $typeFilter !== 'appointment');

        if ($includeAppointments) {
            $bookings = Booking::with(['service.category', 'user', 'details.service', 'transaction.payments'])
                ->where('branch_id', $branch->id)
                ->whereNotIn('status', [BookingStatus::Cancelled])
                ->whereHas('service', function ($q) use ($branch) {
                    $q->whereIn('service_type', ['appointment', 'both'])->orWhereNull('service_type')
                        ->where(fn ($sub) => $sub->where('branch_id', $branch->id)->orWhereNull('branch_id'));
                })
                ->latest('booking_date')
                ->limit(50)
                ->get();

            foreach ($bookings as $b) {
                $trx = $b->transaction;
                $isPaid = $trx && $trx->status === StatusTransaksi::Paid;
                $totalBill = $trx ? (float) $trx->total : ((float) ($b->service?->price ?? 0) + (float) $b->details->sum('subtotal'));
                $paid = $trx ? (float) $trx->payments->whereIn('status', [StatusPembayaran::Completed, StatusPembayaran::Paid])->sum('amount') : 0.0;
                $remaining = max(0, $totalBill - $paid);

                $rawTime = $b->booking_date ? $b->booking_date->copy() : now();
                $timeStr = $b->booking_time ? (is_string($b->booking_time) ? substr($b->booking_time, 0, 5) : $b->booking_time->format('H:i')) : '-';
                $createdTime = $b->created_at ? $b->created_at->translatedFormat('d M Y, H:i').' WIB' : ($b->booking_date ? $b->booking_date->format('d/m/Y').' '.$timeStr : '-');

                if ($isPaid) {
                    $displayStatus = 'Lunas';
                    $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    $actionType = 'none';
                } elseif ($b->status === BookingStatus::Pending) {
                    $displayStatus = 'Menunggu';
                    $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                    $actionType = 'detail';
                } elseif ($b->status === BookingStatus::Confirmed) {
                    $displayStatus = 'Dikerjakan';
                    $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                    $actionType = 'bayar';
                } else {
                    $displayStatus = 'Siap Diambil / Diantar';
                    $badgeClass = 'bg-teal-50 text-teal-700 border border-teal-200';
                    $actionType = 'bayar';
                }

                $items->push([
                    'id' => $b->id,
                    'uuid' => $b->uuid,
                    'type' => 'appointment',
                    'service_id' => $b->service_id,
                    'type_label' => 'Appointment',
                    'type_badge_class' => 'bg-blue-50 text-[#062072] border border-blue-200',
                    'customer_name' => $b->user?->name ?? 'Pelanggan',
                    'service_name' => $b->service?->name ?? 'Layanan',
                    'time' => $timeStr,
                    'created_time' => $createdTime,
                    'estimated_finish' => '—',
                    'pickup_option' => false,
                    'pickup_label' => 'Tidak',
                    'raw_datetime' => $rawTime,
                    'total_amount' => $totalBill,
                    'paid_amount' => $paid,
                    'remaining_amount' => $remaining,
                    'is_estimated_bill' => false,
                    'display_status' => $displayStatus,
                    'status_badge_class' => $badgeClass,
                    'action_type' => $actionType,
                    'detail_url' => route('bookings.show', $b->id),
                    'payment_status_label' => $isPaid ? 'Lunas' : ($paid > 0 ? 'Sebagian' : 'Belum Lunas'),
                    'pay_url' => route('pos.index', ['branch_id' => $branch->id, 'booking_id' => $b->id]),
                ]);
            }
        }

        if ($includeDropOff) {
            $orders = Order::with(['service.category', 'user', 'details.service', 'transaction.payments'])
                ->where('branch_id', $branch->id)
                ->whereNotIn('status', [StatusPesanan::Cancelled])
                ->whereHas('service', function ($q) use ($branch) {
                    $q->whereIn('service_type', ['drop_off', 'both'])->orWhereNull('service_type')
                        ->where(fn ($sub) => $sub->where('branch_id', $branch->id)->orWhereNull('branch_id'));
                })
                ->latest()
                ->limit(50)
                ->get();

            foreach ($orders as $o) {
                $trx = $o->transaction;
                $isPaid = $o->isPaid();
                $totalBill = ($trx && (float) $trx->total > 0) ? (float) $trx->total : $o->calculateBill();
                $paid = $trx ? (float) $trx->payments->whereIn('status', [StatusPembayaran::Completed, StatusPembayaran::Paid])->sum('amount') : 0.0;
                $remaining = max(0, $totalBill - $paid);

                $rawTime = $o->created_at ?? now();
                $timeStr = $o->created_at ? $o->created_at->format('H:i') : '-';
                $createdTime = $o->created_at ? $o->created_at->translatedFormat('d M Y, H:i').' WIB' : '-';
                $estimatedFinish = $o->estimated_finish_at ? $o->estimated_finish_at->translatedFormat('d M Y, H:i').' WIB' : '—';
                $isEstimatedBill = $o->isWeightBased() && ! $o->isWeighed();

                if ($isPaid) {
                    $displayStatus = 'Lunas';
                    $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    $actionType = 'none';
                } elseif ($o->status === StatusPesanan::Pending) {
                    $displayStatus = 'Menunggu';
                    $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                    $actionType = 'terima';
                } elseif ($o->status === StatusPesanan::Received && $o->isWeightBased() && ! $o->isWeighed()) {
                    $displayStatus = 'Belum Ditimbang';
                    $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                    $actionType = 'input_berat';
                } elseif ($o->status === StatusPesanan::Received) {
                    $displayStatus = 'Diterima';
                    $badgeClass = 'bg-slate-100 text-slate-700 border border-slate-200';
                    $actionType = 'detail';
                } elseif ($o->status === StatusPesanan::Processing) {
                    $displayStatus = 'Dikerjakan';
                    $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                    $actionType = 'detail';
                } elseif ($o->status === StatusPesanan::Ready || $o->status === StatusPesanan::Completed) {
                    $displayStatus = 'Siap Diambil / Diantar';
                    $badgeClass = 'bg-teal-50 text-teal-700 border border-teal-200';
                    $actionType = 'bayar';
                } else {
                    $displayStatus = $o->status->label();
                    $badgeClass = 'bg-gray-100 text-gray-700 border border-gray-200';
                    $actionType = 'detail';
                }

                $unit = $o->service?->unit ?? ($o->isWeightBased() ? 'kg' : 'item');
                $estQty = (float) ($o->estimated_quantity ?? 0);
                $estDisplay = $estQty > 0 ? rtrim(rtrim(number_format($estQty, 2, '.', ''), '0'), '.').' '.$unit : '-';

                $items->push([
                    'id' => $o->id,
                    'uuid' => $o->uuid,
                    'type' => 'drop_off',
                    'service_id' => $o->service_id,
                    'type_label' => 'Drop-off',
                    'type_badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200',
                    'customer_name' => $o->user?->name ?? 'Pelanggan',
                    'service_name' => $o->service?->name ?? 'Layanan',
                    'time' => $timeStr,
                    'created_time' => $createdTime,
                    'estimated_finish' => $estimatedFinish,
                    'pickup_option' => (bool) $o->pickup_option,
                    'pickup_label' => $o->pickup_option ? 'Ya' : 'Tidak',
                    'raw_datetime' => $rawTime,
                    'total_amount' => $totalBill,
                    'paid_amount' => $paid,
                    'remaining_amount' => $remaining,
                    'is_estimated_bill' => $isEstimatedBill,
                    'display_status' => $displayStatus,
                    'status_badge_class' => $badgeClass,
                    'action_type' => $actionType,
                    'detail_url' => route('orders.show', $o->id),
                    'receive_url' => route('pos.orders.receive', $o->id),
                    'employee_id' => $o->employee_id,
                    'actual_weight' => $o->actual_weight,
                    'estimated_quantity' => $estQty,
                    'estimated_display' => $estDisplay,
                    'unit' => $unit,
                    'price_per_kg' => (float) ($o->service?->price ?? 0),
                    'is_weight_based' => $o->isWeightBased(),
                    'is_weighed' => $o->isWeighed(),
                    'payment_status_label' => $isPaid ? 'Lunas' : ($paid > 0 ? 'Sebagian' : 'Belum Lunas'),
                    'pay_url' => route('pos.index', ['branch_id' => $branch->id, 'order_id' => $o->id]),
                ]);
            }
        }

        return $items->sortByDesc('raw_datetime')->values();
    }

    /**
     * Data sumber keranjang POS dari booking atau pesanan drop-off.
     */
    public function resolveSourceData(string $type, int|string $id): ?array
    {
        $source = match ($type) {
            'booking' => is_numeric($id)
                ? Booking::with(['service', 'user', 'details.service'])->find($id)
                : Booking::with(['service', 'user', 'details.service'])->where('uuid', $id)->first(),
            'order' => is_numeric($id)
                ? Order::with(['service', 'user', 'details.service'])->find($id)
                : Order::with(['service', 'user', 'details.service'])->where('uuid', $id)->first(),
            default => null,
        };

        if (! $source) {
            return null;
        }

        $items = [];
        if ($source->service) {
            $qty = ($type === 'order') ? (float) ($source->actual_weight ?: $source->estimated_quantity ?: 1) : 1.0;
            $items[] = [
                'service_id' => $source->service->id,
                'name' => $source->service->name,
                'price' => (float) $source->service->price,
                'qty' => $qty,
            ];
        }

        foreach ($source->details as $detail) {
            if ($detail->service && $detail->service_id !== $source->service_id) {
                $items[] = [
                    'service_id' => $detail->service->id,
                    'name' => $detail->service->name,
                    'price' => (float) $detail->price,
                    'qty' => $detail->qty,
                ];
            }
        }

        $fallbackName = $type === 'booking' ? 'Pelanggan Booking' : 'Pelanggan Drop-off';
        $statusVal = $source->status instanceof \BackedEnum ? $source->status->value : (string) $source->status;
        $statusLabel = method_exists($source->status, 'label') ? $source->status->label() : ucfirst($statusVal);

        return [
            'type' => $type,
            'id' => $source->id,
            'status' => $statusVal,
            'status_label' => $statusLabel,
            'is_pending_booking' => ($type === 'booking' && $statusVal === 'pending'),
            'customer_name' => $source->user?->name ?? $fallbackName,
            'customer_phone' => $source->user?->phone ?? '',
            'items' => $items,
        ];
    }
}
