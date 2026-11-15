<?php

namespace App\Http\Controllers\Kasir;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Services\KasirPOS\KasirService;
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
        $kasirBranch = $this->kasirService->getActiveBranchForUser($user, null);

        if ($kasirBranch && $order->branch_id !== $kasirBranch->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini bukan milik cabang Anda.',
            ], 403);
        }

        $order->status = StatusPesanan::Received;

        $isNonWeightBased = ! $order->isWeightBased();

        if ($isNonWeightBased && ! empty($validated['employee_id'])) {
            $order->employee_id = (int) $validated['employee_id'];
        }

        $order->save();

        if ($isNonWeightBased && ! empty($validated['employee_id'])) {
            $employee = Employee::find((int) $validated['employee_id']);
            if ($employee) {
                rescue(fn () => app(OperationalNotificationService::class)->notifyEmployeeNewTask($employee, $order->fresh()));
            }
        }

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
