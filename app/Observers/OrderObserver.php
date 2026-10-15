<?php

namespace App\Observers;

use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Models\Employee;
use App\Models\Order;
use App\Services\Karyawan\KomisiKaryawanService;
use App\Services\OperationalNotificationService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $order->loadMissing(['employee', 'branch', 'service', 'user']);

        if ($order->employee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($order->employee, $order));
        } elseif ($order->branch) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyManagerUnassignedOrder($order->branch, $order));
        }

        if ($order->branch && $order->service) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyBranchCashiersPaymentPending(
                $order->branch,
                $order,
                (float) ($order->service->price ?? 0),
                $order->service->name
            ));
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === StatusPesanan::Completed) {
            $transaction = $order->transaction;
            if ($transaction && $transaction->status === StatusTransaksi::Paid) {
                try {
                    app(KomisiKaryawanService::class)->processTransactionCommission($transaction);
                } catch (\Throwable $e) {
                    Log::error("[OrderObserver] Gagal memproses komisi otomatis order {$order->id}: {$e->getMessage()}", [
                        'order_id' => $order->id,
                        'transaction_id' => $transaction->id,
                        'exception' => $e,
                    ]);
                }
            }
        }

        if ($order->wasChanged('employee_id')) {
            $oldId = $order->getOriginal('employee_id');
            $newId = $order->employee_id;

            if ($oldId && (int) $oldId !== (int) $newId) {
                $oldEmp = Employee::find($oldId);
                if ($oldEmp) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskReassigned($oldEmp, $order));
                }
            }

            if ($newId && (int) $oldId !== (int) $newId) {
                $order->loadMissing('employee');
                if ($order->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($order->employee, $order));
                }
            }
        }

        if ($order->wasChanged('status')) {
            $order->loadMissing(['employee', 'user']);
            $statusVal = $order->status instanceof StatusPesanan ? $order->status->value : (string) $order->status;

            if ($order->status === StatusPesanan::Cancelled) {
                if ($order->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskCancelled($order->employee, $order));
                }
                if ($order->user) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($order->user, $order, 'cancelled'));
                }
            } else {
                if ($order->user) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($order->user, $order, $statusVal));
                }
                if ($order->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeStatusChanged($order->employee, $order, $statusVal));
                }
            }
        }
    }
}
