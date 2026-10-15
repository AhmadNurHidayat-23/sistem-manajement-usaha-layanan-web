<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Employee;
use App\Services\Karyawan\KomisiKaryawanService;
use App\Services\OperationalNotificationService;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $booking->loadMissing(['employee', 'branch', 'service', 'user']);

        if ($booking->employee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($booking->employee, $booking));
        }

        if ($booking->branch && $booking->service) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyBranchCashiersPaymentPending(
                $booking->branch,
                $booking,
                (float) $booking->service->price,
                $booking->service->name
            ));
        }
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status') && $booking->status === BookingStatus::Completed) {
            $transaction = $booking->transaction;
            if ($transaction && $transaction->status === StatusTransaksi::Paid) {
                try {
                    app(KomisiKaryawanService::class)->processTransactionCommission($transaction);
                } catch (\Throwable $e) {
                    Log::error("[BookingObserver] Gagal memproses komisi otomatis booking {$booking->id}: {$e->getMessage()}", [
                        'booking_id' => $booking->id,
                        'transaction_id' => $transaction->id,
                        'exception' => $e,
                    ]);
                }
            }
        }

        if ($booking->wasChanged('employee_id')) {
            $oldId = $booking->getOriginal('employee_id');
            $newId = $booking->employee_id;

            if ($oldId && (int) $oldId !== (int) $newId) {
                $oldEmp = Employee::find($oldId);
                if ($oldEmp) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskReassigned($oldEmp, $booking));
                }
            }

            if ($newId && (int) $oldId !== (int) $newId) {
                $booking->loadMissing('employee');
                if ($booking->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($booking->employee, $booking));
                }
            }
        } elseif ($booking->wasChanged('booking_date') || $booking->wasChanged('booking_time')) {
            $booking->loadMissing('employee');
            if ($booking->employee) {
                app(OperationalNotificationService::class)->notifyEmployeeScheduleChanged($booking->employee, $booking);
            }
        }

        if ($booking->wasChanged('status')) {
            $booking->loadMissing(['employee', 'user']);
            $statusVal = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;

            if ($booking->status === BookingStatus::Cancelled) {
                if ($booking->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskCancelled($booking->employee, $booking));
                }
                if ($booking->user) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($booking->user, $booking, 'cancelled'));
                }
            } else {
                if ($booking->user) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyCustomerStatus($booking->user, $booking, $statusVal));
                }
                if ($booking->employee) {
                    rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeStatusChanged($booking->employee, $booking, $statusVal));
                }
            }
        }
    }
}
