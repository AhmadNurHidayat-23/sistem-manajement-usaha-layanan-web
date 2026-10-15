<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\PromotionStatus;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\MembershipPromo\PromoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganPromoController extends Controller
{
    public function __construct(
        protected PromoService $promoService
    ) {}

    /**
     * Customer promo catalog.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user && $user->role?->name === 'manager') {
            abort(403, 'Akses ditolak untuk akun manajer.');
        }

        if ($user) {
            $promotions = $this->promoService->getPromotions($user, ['active_only' => true], 20);
        } else {
            $today = Carbon::today();
            $promotions = Promotion::with('branch')
                ->withCount('usages')
                ->where('status', PromotionStatus::Active)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->paginate(20);
        }

        return view('pelanggan.promo.index', compact('promotions'));
    }

    /**
     * Katalog voucher promo.
     */
    public function publicPromos(Request $request): View
    {
        $user = $request->user();

        if ($user && $user->role?->name === 'manager') {
            abort(403, 'Akses ditolak untuk akun manajer.');
        }

        $promotions = $this->promoService->getPromotions($user, ['active_only' => true], 20);

        return view('pelanggan.promo.public', compact('promotions'));
    }
}
