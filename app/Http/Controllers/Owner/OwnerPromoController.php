<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPromo\StorePromoRequest;
use App\Http\Requests\MembershipPromo\UpdatePromoRequest;
use App\Http\Requests\MembershipPromo\ValidatePromoRequest;
use App\Models\Promotion;
use App\Services\AktivitasService;
use App\Services\MembershipPromo\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerPromoController extends Controller
{
    public function __construct(
        protected PromoService $promoService,
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Daftar promotions for management.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['branch_id', 'status', 'discount_type', 'q']);

        $promotions = $this->promoService->getPromotions($user, $filters);
        $branches = $this->promoService->getAccessibleBranches($user);

        $branchId = ! empty($filters['branch_id']) ? (int) $filters['branch_id'] : null;
        $statistics = $this->promoService->getPromoStatistics($branchId);

        return view('owner.promo.index', compact('promotions', 'branches', 'filters', 'statistics'));
    }

    /**
     * Form tambah promotion.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $branches = $this->promoService->getAccessibleBranches($user);

        return view('owner.promo.create', compact('branches'));
    }

    /**
     * Simpan promotion baru.
     */
    public function store(StorePromoRequest $request): RedirectResponse
    {
        $promo = $this->promoService->createPromo($request->user(), $request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Tambah Promo', 'promo', "Kode promo '{$promo->code}' ({$promo->name}) dibuat.");

        return redirect()->route('promotions.index')
            ->with('success', "Kode promo '{$promo->code}' berhasil dibuat.");
    }

    /**
     * Form edit promotion.
     */
    public function edit(Request $request, Promotion $promotion): View
    {
        $user = $request->user();
        $branches = $this->promoService->getAccessibleBranches($user);

        return view('owner.promo.edit', compact('promotion', 'branches'));
    }

    /**
     * Perbarui promotion.
     */
    public function update(UpdatePromoRequest $request, Promotion $promotion): RedirectResponse
    {
        $updated = $this->promoService->updatePromo($request->user(), $promotion, $request->validated());

        $this->aktivitasService->catatDariRequest($request, 'Edit Promo', 'promo', "Promo '{$updated->code}' diperbarui.");

        return redirect()->route('promotions.index')
            ->with('success', "Promo '{$updated->code}' berhasil diperbarui.");
    }

    /**
     * Ubah status promo.
     */
    public function toggleStatus(Request $request, Promotion $promotion): RedirectResponse
    {
        $updated = $this->promoService->togglePromoStatus($request->user(), $promotion);
        $statusText = $updated->status->value === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        $this->aktivitasService->catatDariRequest($request, 'Ubah Status Promo', 'promo', "Promo '{$updated->code}' {$statusText}.");

        return back()->with('success', "Promo '{$updated->code}' berhasil {$statusText}.");
    }

    /**
     * Validasi kode promo.
     */
    public function validatePromo(ValidatePromoRequest $request): JsonResponse
    {
        $result = $this->promoService->validatePromo(
            $request->input('code'),
            (float) $request->input('amount'),
            $request->input('branch_id') ? (int) $request->input('branch_id') : null,
            $request->user()
        );

        if ($result['valid']) {
            $result['discount_amount'] = $result['discount'] ?? 0;
        }

        $statusCode = $result['valid'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Setujui pengajuan promo cabang (Owner only).
     */
    public function approve(Request $request, Promotion $promotion): RedirectResponse
    {
        $updated = $this->promoService->approvePromo($request->user(), $promotion);

        $this->aktivitasService->catatDariRequest($request, 'Setujui Promo', 'promo', "Promo '{$updated->code}' disetujui.");

        return back()->with('success', "Pengajuan promo '{$updated->code}' berhasil disetujui.");
    }

    /**
     * Tolak pengajuan promo cabang (Owner only).
     */
    public function reject(Request $request, Promotion $promotion): RedirectResponse
    {
        $updated = $this->promoService->rejectPromo($request->user(), $promotion);

        $this->aktivitasService->catatDariRequest($request, 'Tolak Promo', 'promo', "Promo '{$updated->code}' ditolak.");

        return back()->with('success', "Pengajuan promo '{$updated->code}' berhasil ditolak.");
    }
}
