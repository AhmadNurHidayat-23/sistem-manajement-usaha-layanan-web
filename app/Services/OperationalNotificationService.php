<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\OperationalNotification;
use App\Services\Notifications\CashierNotificationService;
use App\Services\Notifications\CustomerNotificationService;
use App\Services\Notifications\EmployeeNotificationService;
use App\Services\Notifications\NotificationHelper;
use Illuminate\Support\Collection;

class OperationalNotificationService
{
    public function __construct(
        protected EmployeeNotificationService $employeeService,
        protected CashierNotificationService $cashierService,
        protected CustomerNotificationService $customerService,
    ) {}

    public function notifyEmployeeNewTask(Employee $employee, Booking|Order $task): void
    {
        $this->employeeService->notifyNewTask($employee, $task);
    }

    public function notifyEmployeeTaskReassigned(Employee $oldEmployee, Booking|Order $task): void
    {
        $this->employeeService->notifyTaskReassigned($oldEmployee, $task);
    }

    public function notifyEmployeeScheduleChanged(Employee $employee, Booking $booking): void
    {
        $this->employeeService->notifyScheduleChanged($employee, $booking);
    }

    public function notifyEmployeeTaskCancelled(Employee $employee, Booking|Order $task): void
    {
        $this->employeeService->notifyTaskCancelled($employee, $task);
    }

    public function notifyEmployeeStatusChanged(Employee $employee, Booking|Order $task, string $status): void
    {
        $this->employeeService->notifyStatusChanged($employee, $task, $status);
    }

    public function notifyManagerUnassignedOrder(Branch $branch, Order $order): void
    {
        $managers = NotificationHelper::getBranchStaff($branch->id, 'manager');
        $service = $order->service?->name ?? 'Layanan';

        foreach ($managers as $manager) {
            $key = "manager_unassigned_order_{$order->id}_{$manager->id}";
            if (NotificationHelper::hasKey($manager, $key)) {
                continue;
            }

            $manager->notify(new OperationalNotification(
                title: 'Pesanan Memerlukan Penugasan',
                message: "Pesanan #{$order->id} ({$service}) di cabang {$branch->name} membutuhkan penugasan petugas.",
                category: 'manager_alert',
                statusLevel: 'warning',
                actionUrl: route('orders.show', $order),
                actionLabel: 'Tugaskan Petugas',
                details: ['order_id' => $order->id, 'branch_id' => $branch->id],
                idempotencyKey: $key,
            ));
        }
    }

    public function notifyBranchCashiersPaymentPending(Branch $branch, Booking|Order|Transaction $source, float $amount, string $serviceName): void
    {
        $this->cashierService->notifyPaymentPending($branch, $source, $amount, $serviceName);
    }

    public function notifyBranchCashiersPaymentSuccess(Transaction $transaction, ?Payment $payment = null): void
    {
        $this->cashierService->notifyPaymentSuccess($transaction, $payment);
    }

    public function notifyCustomerStatus(User $customer, Booking|Order $source, string $event): void
    {
        $this->customerService->notifyStatus($customer, $source, $event);
    }

    public function getBranchCashiers(int $branchId): Collection
    {
        return NotificationHelper::getBranchStaff($branchId, 'cashier');
    }

    public function getBranchManagers(int $branchId): Collection
    {
        return NotificationHelper::getBranchStaff($branchId, 'manager');
    }
}
