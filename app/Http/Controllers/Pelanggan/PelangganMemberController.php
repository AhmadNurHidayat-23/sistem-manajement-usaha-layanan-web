<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Services\MembershipPromo\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganMemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService
    ) {}

    /**
     * Halaman member pelanggan.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $membership = $user->membership()->first();

        if ($membership) {
            $membership = $this->memberService->getMemberDetails($user, $membership);
        }

        $packages = $this->memberService->getSubscriptionPackages();

        return view('pelanggan.member.index', compact('membership', 'packages'));
    }

    /**
     * Customer self-registration as member.
     */
    public function register(Request $request): RedirectResponse
    {
        $user = $request->user();
        $member = $this->memberService->registerSelfAsMember($user);

        return redirect()->route('customer.member')
            ->with('success', "Selamat! Anda resmi menjadi member dengan nomor {$member->member_code} dan mendapatkan 10 poin bonus.");
    }
}
