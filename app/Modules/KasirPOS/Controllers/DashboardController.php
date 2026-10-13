<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KasirPOS\Services\KasirService;
use App\Modules\KasirPOS\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected KasirService $kasirService,
        protected TransaksiService $transaksiService,
    ) {}

    /**
     * Tampilkan dashboard kasir dengan ringkasan shift dan antrian aktif.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $accessibleBranches = $this->kasirService->getAccessibleBranches($user);

        $selectedBranchId = $request->input('branch_id');
        $activeBranch = $this->kasirService->getActiveBranchForUser($user, $selectedBranchId ? (int) $selectedBranchId : null);

        $activeShift = null;
        $shiftSummary = null;
        $pendingPayments = collect();
        $currentFilter = (string) $request->input('type', 'all');

        if ($activeBranch) {
            $activeShift = $this->kasirService->getActiveShift($user, $activeBranch->id);
            if ($activeShift) {
                $shiftSummary = $this->kasirService->getShiftSummary($activeShift);
            }

            $pendingPayments = $this->transaksiService->getPendingPayments($activeBranch, $currentFilter);
        }

        return view('modules.kasir-pos.kasir.dashboard.index', compact(
            'accessibleBranches',
            'activeBranch',
            'activeShift',
            'shiftSummary',
            'pendingPayments',
            'currentFilter'
        ));
    }
}
