<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasirPOS\CloseShiftRequest;
use App\Http\Requests\KasirPOS\OpenShiftRequest;
use App\Models\CashierShift;
use App\Services\AktivitasService;
use App\Services\KasirPOS\KasirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(
        protected KasirService $kasirService,
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Tampilkan halaman operasional Shift Kasir tersendiri.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $accessibleBranches = $this->kasirService->getAccessibleBranches($user);

        $selectedBranchId = $request->input('branch_id');
        $activeBranch = $this->kasirService->getActiveBranchForUser($user, $selectedBranchId ? (int) $selectedBranchId : null);

        $activeShift = null;
        $shiftSummary = null;
        $shiftHistory = collect();

        if ($activeBranch) {
            $activeShift = $this->kasirService->getActiveShift($user, $activeBranch->id);
            if ($activeShift) {
                $shiftSummary = $this->kasirService->getShiftSummary($activeShift);
            }

            $shiftHistory = CashierShift::where('user_id', $user->id)
                ->where('branch_id', $activeBranch->id)
                ->with(['branch', 'user'])
                ->latest('opened_at')
                ->take(10)
                ->get();
        }

        return view('kasir.shift.page', compact(
            'accessibleBranches',
            'activeBranch',
            'activeShift',
            'shiftSummary',
            'shiftHistory'
        ));
    }

    /**
     * Buka shift kasir baru dengan modal awal laci kasir.
     */
    public function openShift(OpenShiftRequest $request): RedirectResponse
    {
        $shift = $this->kasirService->openShift(
            $request->user(),
            (int) $request->input('branch_id'),
            (float) $request->input('opening_cash')
        );

        $this->aktivitasService->catatDariRequest($request, 'Buka Shift', 'kasir', "Shift kasir dibuka di cabang ID {$shift->branch_id} dengan modal Rp ".number_format($shift->opening_cash, 0, ',', '.').'.');

        $successMsg = 'Shift kasir berhasil dibuka dengan modal awal Rp '.number_format($shift->opening_cash, 0, ',', '.').'.';

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $successMsg);
        }

        return redirect()->route('pos.index', ['branch_id' => $shift->branch_id])
            ->with('success', $successMsg);
    }

    /**
     * Tutup shift kasir yang sedang aktif dan hitung selisih kas.
     */
    public function closeShift(CloseShiftRequest $request): RedirectResponse
    {
        $user = $request->user();
        $branchId = $request->input('branch_id');
        $activeShift = $this->kasirService->getActiveShift($user, $branchId ? (int) $branchId : null);

        if (! $activeShift) {
            $errorMsg = 'Tidak ditemukan shift kasir aktif yang dapat ditutup.';
            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))->with('error', $errorMsg);
            }

            return redirect()->route('pos.index')
                ->with('error', $errorMsg);
        }

        $closedShift = $this->kasirService->closeShift(
            $activeShift,
            (float) $request->input('closing_cash'),
            $request->input('notes')
        );

        $diffText = '';
        if ($closedShift->cash_difference == 0) {
            $diffText = 'Kas seimbang (tidak ada selisih).';
        } elseif ($closedShift->cash_difference > 0) {
            $diffText = 'Selisih lebih Rp '.number_format($closedShift->cash_difference, 0, ',', '.').'.';
        } else {
            $diffText = 'Selisih kurang Rp '.number_format(abs($closedShift->cash_difference), 0, ',', '.').'.';
        }

        $this->aktivitasService->catatDariRequest($request, 'Tutup Shift', 'kasir', "Shift kasir ditutup. {$diffText}");

        $successMsg = "Shift kasir berhasil ditutup. {$diffText}";

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', $successMsg);
        }

        return redirect()->route('pos.index')
            ->with('success', $successMsg);
    }
}
