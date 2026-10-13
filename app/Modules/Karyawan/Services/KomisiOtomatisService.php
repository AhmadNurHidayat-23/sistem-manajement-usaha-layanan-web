<?php

namespace App\Modules\Karyawan\Services;

use App\Enums\BookingStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KomisiOtomatisService
{
    /**
     * Hitung dan catat estimasi komisi otomatis dari transaksi yang telah diselesaikan (KRY-F-004).
     *
     * @return Collection<int, EmployeeCommission>
     */
    public function process(Transaction $transaction): Collection
    {
        if ($transaction->status !== StatusTransaksi::Paid) {
            return collect();
        }

        if ($transaction->booking_id) {
            $booking = $transaction->booking ?? Booking::find($transaction->booking_id);
            if (! $booking || $booking->status !== BookingStatus::Completed) {
                return collect();
            }
        }

        if ($transaction->order_id) {
            $order = $transaction->order ?? Order::find($transaction->order_id);
            if (! $order || $order->status !== StatusPesanan::Completed) {
                return collect();
            }
        }

        $employeeId = $transaction->booking?->employee_id
            ?? $transaction->order?->employee_id
            ?? $transaction->employee_id;

        if (! $employeeId && $transaction->booking_id) {
            $employeeId = Booking::where('id', $transaction->booking_id)->value('employee_id');
        }

        if (! $employeeId && $transaction->order_id) {
            $employeeId = Order::where('id', $transaction->order_id)->value('employee_id');
        }

        if (! $employeeId) {
            Log::info("[KomisiOtomatisService] Transaksi {$transaction->id} tanpa penugasan staf sah, kalkulasi dilewati.", [
                'transaction_id' => $transaction->id,
            ]);

            return collect();
        }

        $employee = Employee::find($employeeId);
        if (! $employee) {
            return collect();
        }

        try {
            return DB::transaction(function () use ($transaction, $employee) {
                $lockedTx = Transaction::where('id', $transaction->id)->lockForUpdate()->first();
                if (! $lockedTx || $lockedTx->status !== StatusTransaksi::Paid) {
                    return collect();
                }

                $details = TransactionDetail::with('service')
                    ->where('transaction_id', $lockedTx->id)
                    ->get();

                if ($details->isEmpty()) {
                    return collect();
                }

                $created = collect();
                $grouped = $details->groupBy('service_id');

                foreach ($grouped as $serviceId => $serviceDetails) {
                    $service = $serviceDetails->first()->service ?? Service::find($serviceId);
                    if (! $service || $service->commission_type === null || $service->commission_value === null) {
                        continue;
                    }

                    $exists = EmployeeCommission::where('transaction_id', $lockedTx->id)
                        ->where('service_id', $service->id)
                        ->where('employee_id', $employee->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $commType = $service->commission_type;
                    $commVal = (float) $service->commission_value;
                    $totalQty = (float) $serviceDetails->sum('qty');
                    $totalSubtotal = (float) $serviceDetails->sum(fn ($d) => (float) ($d->subtotal ?: ($d->price * $d->qty)));

                    if ($commType === CommissionType::Percentage || $commType === CommissionType::Percentage->value) {
                        $amount = round($totalSubtotal * ($commVal / 100), 2);
                    } else {
                        $amount = round($commVal * max(1, $totalQty), 2);
                    }

                    $commRecord = EmployeeCommission::create([
                        'employee_id' => $employee->id,
                        'service_id' => $service->id,
                        'transaction_id' => $lockedTx->id,
                        'commission_type' => $commType instanceof CommissionType ? $commType->value : (string) $commType,
                        'commission_value' => $commVal,
                        'commission_amount' => $amount,
                        'status' => CommissionStatus::Pending->value,
                    ]);

                    $created->push($commRecord);
                }

                return $created;
            });
        } catch (\Throwable $e) {
            Log::error("[KomisiOtomatisService] Gagal memproses komisi transaksi {$transaction->id}: {$e->getMessage()}", [
                'transaction_id' => $transaction->id,
                'employee_id' => $employee->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
