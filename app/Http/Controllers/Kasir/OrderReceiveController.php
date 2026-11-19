<?php

namespace App\Http\Controllers\Kasir;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Services\KasirPOS\KasirService;
use App\Services\NotifikasiService;
use App\Services\OperationalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderReceiveController extends Controller
{
    public function __construct(
        protected KasirService $kasirService,
    ) {}

    /**
     * Kasir menerima barang drop-off yang sudah tiba di toko.
     * Untuk pesanan non-kiloan, karyawan pengerjaan dipilih langsung.
     */
    public function receiveOrder(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        if ($order->status !== StatusPesanan::Pending) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pesanan berstatus Menunggu yang dapat diterima.',
            ], 422);
        }

        $user = $request->user();
        if ($order->branch) {
            try {
                $this->kasirService->authorizeBranchAccess($user, $order->branch);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengelola pesanan cabang ini.',
                ], 403);
            }
        }

        $order->status = StatusPesanan::Received;
        $isNonWeightBased = ! $order->isWeightBased();
        $assignedEmployee = null;

        if ($isNonWeightBased) {
            $employeeId = $validated['employee_id'] ?? null;
            if (! $employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Petugas pengerjaan wajib dipilih untuk layanan non-kiloan.',
                ], 422);
            }

            $assignedEmployee = Employee::where('id', $employeeId)
                ->where('branch_id', $order->branch_id)
                ->where('status', 'active')
                ->first();

            if (! $assignedEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Petugas yang dipilih tidak valid atau bukan karyawan aktif cabang ini.',
                ], 422);
            }

            $order->employee_id = $assignedEmployee->id;
        }

        $order->save();

        if ($isNonWeightBased && $assignedEmployee) {
            rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($assignedEmployee, $order->fresh()));
        }

        rescue(fn () => app(NotifikasiService::class)->notifikasiStatusOrder($order->fresh()));

        return response()->json([
            'success' => true,
            'message' => "Pesanan #{$order->id} berhasil diterima.",
            'is_weight_based' => $order->isWeightBased(),
        ]);
    }

    /**
     * Ambil daftar karyawan aktif suatu cabang (untuk dropdown penugasan).
     */
    public function branchEmployees(Branch $branch): JsonResponse
    {
        $employees = Employee::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name ?? $e->user?->name ?? 'Karyawan',
            ]);

        return response()->json($employees);
    }
}
