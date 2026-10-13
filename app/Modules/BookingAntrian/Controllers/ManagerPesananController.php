<?php

namespace App\Modules\BookingAntrian\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Order;
use App\Modules\BookingAntrian\Requests\StorePesananRequest;
use App\Modules\BookingAntrian\Requests\UpdateStatusPesananRequest;
use App\Modules\BookingAntrian\Services\BookingService;
use App\Modules\BookingAntrian\Services\StatusPekerjaanService;
use App\Services\NotifikasiService;
use App\Services\OperationalNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ManagerPesananController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected StatusPekerjaanService $statusService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Daftar pesanan drop-off.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'branch_id']);
        $orders = $this->bookingService->getOrdersForUser($request->user(), $filters);
        $branches = $this->bookingService->getActiveBranches();

        return view('modules.booking-antrian.manager.index', compact('orders', 'branches', 'filters'));
    }

    /**
     * Form tambah drop-off order.
     */
    public function create(): View
    {
        $this->authorize('create', Order::class);

        $branches = $this->bookingService->getActiveBranches('drop_off');
        $services = $this->bookingService->getActiveServices();

        return view('modules.booking-antrian.manager.create', compact('branches', 'services'));
    }

    /**
     * Simpan drop-off order baru.
     */
    public function store(StorePesananRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->bookingService->createOrder($request->user(), $request->validated());

        rescue(fn () => $this->notifikasiService->notifikasiOrderBaru($order->fresh()));

        $fresh = $order->fresh(['employee', 'branch', 'service', 'user']);
        if ($fresh->employee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($fresh->employee, $fresh));
        } elseif ($fresh->branch) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyManagerUnassignedOrder($fresh->branch, $fresh));
        }

        $finishText = $order->estimated_finish_at ? $order->estimated_finish_at->format('d M Y H:i') : '-';

        return redirect()->route('orders.show', $order)
            ->with('success', "Order drop-off berhasil dibuat. Estimasi selesai: {$finishText}.");
    }

    /**
     * Tampilkan detail order.
     */
    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['branch', 'service.category', 'user', 'details.service']);

        return view('modules.booking-antrian.manager.show', compact('order'));
    }

    /**
     * Update status pesanan.
     */
    public function updateStatus(UpdateStatusPesananRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $oldEmployeeId = $order->employee_id;
            if ($request->filled('employee_id') && in_array($request->user()->role?->name, ['owner', 'manager'], true)) {
                $newEmployeeId = (int) $request->input('employee_id');
                $order->update(['employee_id' => $newEmployeeId]);

                if ($oldEmployeeId && (int) $oldEmployeeId !== $newEmployeeId) {
                    $oldEmp = Employee::find($oldEmployeeId);
                    if ($oldEmp) {
                        rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskReassigned($oldEmp, $order));
                    }
                }
                if ($newEmployeeId && (int) $oldEmployeeId !== $newEmployeeId) {
                    $newEmp = Employee::find($newEmployeeId);
                    if ($newEmp) {
                        rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($newEmp, $order));
                    }
                }
            }

            $updated = $this->statusService->updateOrderStatus($request->user(), $order, $request->input('status'));

            rescue(fn () => $this->notifikasiService->notifikasiStatusOrder($order->fresh()));

            return back()->with('success', 'Status order berhasil diperbarui.');
        } catch (AuthorizationException|InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Batalkan pesanan.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->bookingService->cancelOrder($request->user(), $order);

            $fresh = $order->fresh(['employee', 'service', 'user']);
            if ($fresh->employee) {
                rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeTaskCancelled($fresh->employee, $fresh));
            }

            rescue(fn () => $this->notifikasiService->notifikasiStatusOrder($fresh));

            return redirect()->route('orders.index')
                ->with('success', 'Order berhasil dibatalkan.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
