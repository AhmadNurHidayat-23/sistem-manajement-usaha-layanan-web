<?php

namespace App\Modules\MembershipPromo\Controllers;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Role;
use App\Models\User;
use App\Modules\MembershipPromo\Requests\RedeemPointRequest;
use App\Modules\MembershipPromo\Requests\StoreMemberRequest;
use App\Modules\MembershipPromo\Requests\UpdateMemberRequest;
use App\Modules\MembershipPromo\Services\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService
    ) {}

    /**
     * Daftar members.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['status', 'q']);

        $members = $this->memberService->getMembers($user, $filters);
        $statistics = $this->memberService->getMemberStatistics();

        return view('modules.membership-promo.admin.member.index', compact('members', 'filters', 'statistics'));
    }

    /**
     * Form registrasi member.
     */
    public function create(Request $request): View
    {
        // User pelanggan tanpa membership
        $customerRole = Role::where('name', 'customer')->first();
        $customersQuery = User::query();

        if ($customerRole) {
            $customersQuery->where('role_id', $customerRole->id);
        }

        $customers = $customersQuery->doesntHave('membership')
            ->orderBy('name')
            ->get();

        return view('modules.membership-promo.admin.member.create', compact('customers'));
    }

    /**
     * Simpan member baru.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $member = $this->memberService->createMember($request->user(), $request->validated());

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('success', "Member '{$member->member_code}' ({$member->user->name}) berhasil didaftarkan dengan bonus 10 poin.");
        }

        return redirect()->route('members.show', $member)
            ->with('success', "Member '{$member->member_code}' berhasil didaftarkan dengan bonus 10 poin selamat datang.");
    }

    /**
     * Tampilkan detail member profile, point balance, and point transaction history.
     */
    public function show(Request $request, Membership $membership): View
    {
        $user = $request->user();
        $membership = $this->memberService->getMemberDetails($user, $membership);

        return view('modules.membership-promo.admin.member.show', compact('membership'));
    }

    /**
     * Perbarui status member.
     */
    public function update(UpdateMemberRequest $request, Membership $membership): RedirectResponse
    {
        $validated = $request->validated();
        $updatedMember = $this->memberService->updateMember($membership, [
            'status' => MembershipStatus::from($validated['status']),
        ]);

        return redirect()->back()
            ->with('success', "Status member '{$updatedMember->member_code}' berhasil diubah menjadi {$updatedMember->status->value}.");
    }

    /**
     * Customer 1-click self-registration as a member.
     */
    public function registerSelf(Request $request): RedirectResponse
    {
        $user = $request->user();
        $member = $this->memberService->registerSelfAsMember($user);

        return redirect()->route('members.my-membership')
            ->with('success', "Selamat! Anda telah resmi menjadi member dengan nomor {$member->member_code} dan mendapatkan 10 poin bonus perdana.");
    }

    /**
     * Tukar poin reward.
     */
    public function redeem(RedeemPointRequest $request, Membership $membership): RedirectResponse
    {
        $validated = $request->validated();

        $this->memberService->redeemPoints(
            $request->user(),
            $membership,
            (int) $validated['points'],
            $validated['reward_description']
        );

        return redirect()->back()
            ->with('success', "Berhasil menukar {$validated['points']} poin untuk {$validated['reward_description']}.");
    }

    /**
     * Portal member pelanggan.
     */
    public function myMembership(Request $request): View
    {
        $user = $request->user();
        $membership = $user->membership;

        if ($membership) {
            $membership = $this->memberService->getMemberDetails($user, $membership);
        }

        $packages = $this->memberService->getSubscriptionPackages();

        return view('modules.membership-promo.customer.member.my-membership', compact('membership', 'packages'));
    }

    /**
     * View membership subscription packages and VIP tiers.
     */
    public function packages(): View
    {
        $packages = $this->memberService->getSubscriptionPackages();

        return view('modules.membership-promo.customer.member.packages', compact('packages'));
    }
}
