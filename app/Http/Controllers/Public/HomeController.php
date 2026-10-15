<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\Katalog\LayananService;
use App\Services\Katalog\UlasanService;
use App\Services\MembershipPromo\PromoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected LayananService $layananService,
        protected PromoService $promoService,
        protected UlasanService $ulasanService
    ) {}

    /**
     * Landing page dan katalog layanan.
     * Pengguna staf internal langsung dialihkan ke dashboard role masing-masing,
     * sedangkan customer dan guest dapat mengakses landing page dan katalog layanan.
     */
    public function home(Request $request): View|RedirectResponse
    {
        if (Auth::check() && $request->user()->role?->name !== UserRole::Customer->value) {
            return redirect()->to($request->user()->getDashboardUrl());
        }

        $branchId = $request->query('branch_id') ?? $request->query('branch');
        $categoryId = $request->query('category_id') ?? $request->query('category');
        $currentBranch = null;

        if ($branchId) {
            $currentBranch = is_numeric($branchId)
                ? Branch::where('id', $branchId)->where('is_active', true)->first()
                : Branch::where('name', 'like', "%{$branchId}%")->where('is_active', true)->first();
        }

        $branchFilterId = $currentBranch?->id;

        $services = $this->layananService->getLandingCatalog($branchFilterId);
        $promotions = $this->promoService->getActivePromotions($branchFilterId, 3);
        $reviews = $this->ulasanService->getLandingReviews($branchFilterId, 3);
        $userMembership = $request->user()?->membership;
        $availableBranches = Branch::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true)->with(['category', 'images']);
            }])
            ->orderBy('id')
            ->get();

        return view('landing.index', compact(
            'services',
            'promotions',
            'reviews',
            'userMembership',
            'currentBranch',
            'availableBranches'
        ));
    }
}
