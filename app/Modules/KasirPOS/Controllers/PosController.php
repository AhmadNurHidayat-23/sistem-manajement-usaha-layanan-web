<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Modules\KasirPOS\Services\KasirService;
use App\Modules\KasirPOS\Services\TransaksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected KasirService $kasirService,
        protected TransaksiService $transaksiService,
    ) {}

    /**
     * Tampilkan terminal utama POS kasir.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $accessibleBranches = $this->kasirService->getAccessibleBranches($user);

        $selectedBranchId = $request->input('branch_id');
        $activeBranch = $this->kasirService->getActiveBranchForUser($user, $selectedBranchId ? (int) $selectedBranchId : null);

        $activeShift = null;
        $shiftSummary = null;
        $services = collect();
        $eligibleBookings = collect();
        $eligibleOrders = collect();

        if ($activeBranch) {
            // Jika booking_id atau order_id pada query string telah lunas, arahkan langsung ke struk
            if ($bParam = $request->query('booking_id')) {
                $paidTx = Transaction::where(function ($q) use ($bParam) {
                    $q->where('booking_id', $bParam)
                        ->orWhereHas('booking', fn ($b) => $b->where('uuid', $bParam));
                })->where('status', StatusTransaksi::Paid)->first();

                if ($paidTx) {
                    return redirect()->route('pos.transactions.receipt', $paidTx)
                        ->with('info', 'Booking ini sudah dibayar lunas.');
                }
            }

            if ($oParam = $request->query('order_id')) {
                $paidTx = Transaction::where(function ($q) use ($oParam) {
                    $q->where('order_id', $oParam)
                        ->orWhereHas('order', fn ($o) => $o->where('uuid', $oParam));
                })->where('status', StatusTransaksi::Paid)->first();

                if ($paidTx) {
                    return redirect()->route('pos.transactions.receipt', $paidTx)
                        ->with('info', 'Pesanan ini sudah dibayar lunas.');
                }
            }

            $activeShift = $this->kasirService->getActiveShift($user, $activeBranch->id);
            if ($activeShift) {
                $shiftSummary = $this->kasirService->getShiftSummary($activeShift);
            }

            $services = Service::where('is_active', true)
                ->where(function ($q) use ($activeBranch) {
                    $q->whereNull('branch_id')
                        ->orWhere('branch_id', $activeBranch->id);
                })
                ->with('category')
                ->orderBy('name')
                ->get();

            $eligibleBookings = $this->transaksiService->getEligibleBookings($activeBranch->id);
            $eligibleOrders = $this->transaksiService->getEligibleOrders($activeBranch->id);
        }

        return view('modules.kasir-pos.kasir.pos.index', compact(
            'accessibleBranches',
            'activeBranch',
            'activeShift',
            'shiftSummary',
            'services',
            'eligibleBookings',
            'eligibleOrders'
        ));
    }

    /**
     * Ambil data detail booking atau order untuk dimasukkan ke keranjang POS.
     */
    public function sourceData(Request $request): JsonResponse
    {
        $type = (string) $request->query('type');
        $id = (string) $request->query('id');

        if (! in_array($type, ['booking', 'order'], true)) {
            return response()->json(['error' => 'Tipe sumber tidak valid'], 400);
        }

        $data = $this->transaksiService->resolveSourceData($type, $id);
        if (! $data) {
            $label = $type === 'booking' ? 'Booking' : 'Pesanan';

            return response()->json(['error' => "{$label} tidak ditemukan"], 404);
        }

        return response()->json($data);
    }
}
