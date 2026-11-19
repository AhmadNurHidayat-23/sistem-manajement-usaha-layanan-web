<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\PromotionStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganDashboardController extends Controller
{
    /**
     * Dashboard pelanggan yang disederhanakan dan terintegrasi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 3 Usaha (masing-masing satu kali) beserta layanan aktif
        $branches = Branch::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true)->with('category')->orderBy('name');
            }])
            ->orderBy('id')
            ->get();

        // Membership pelanggan
        $membership = $user->membership;

        // Promo aktif yang relevan
        $activePromos = Promotion::where('status', PromotionStatus::Active)
            ->where('start_date', '<=', Carbon::today())
            ->where('end_date', '>=', Carbon::today())
            ->latest('id')
            ->take(2)
            ->get();

        return view('pelanggan.dashboard', compact(
            'branches',
            'membership',
            'activePromos',
        ));
    }
}
