<?php

namespace Tests\Feature\MembershipPromo;

use App\Modules\MembershipPromo\Services\MemberService;
use Tests\TestCase;

class MembershipUniversalBenefitsTest extends TestCase
{
    public function test_paket_membership_memiliki_copy_benefit_universal_tanpa_istilah_laundry(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $packages = $memberService->getSubscriptionPackages();

        $this->assertCount(4, $packages);

        $forbiddenTerms = ['cucian', 'kiloan', 'parfum laundry', 'dry clean'];

        foreach ($packages as $package) {
            $this->assertArrayHasKey('name', $package);
            $this->assertArrayHasKey('description', $package);
            $this->assertArrayHasKey('features', $package);

            $allText = strtolower($package['description'].' '.implode(' ', $package['features']));

            foreach ($forbiddenTerms as $term) {
                $this->assertStringNotContainsString(
                    $term,
                    $allText,
                    "Paket {$package['name']} masih mengandung istilah spesifik: '{$term}'."
                );
            }
        }
    }

    public function test_paket_membership_tidak_menjanjikan_fitur_unsupported(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $packages = $memberService->getSubscriptionPackages();

        $unsupportedTerms = ['whatsapp', 'antar-jemput', 'tanpa antre', 'garansi pengerjaan', 'antrean prioritas'];

        foreach ($packages as $package) {
            $allText = strtolower($package['description'].' '.implode(' ', $package['features']));

            foreach ($unsupportedTerms as $term) {
                $this->assertStringNotContainsString(
                    $term,
                    $allText,
                    "Paket {$package['name']} masih menjanjikan fitur unsupported: '{$term}'."
                );
            }
        }
    }

    public function test_paket_membership_menyediakan_benefit_layanan_umum(): void
    {
        /** @var MemberService $memberService */
        $memberService = app(MemberService::class);
        $packages = $memberService->getSubscriptionPackages();

        $platinum = collect($packages)->firstWhere('level', 'Platinum');

        $this->assertNotNull($platinum);
        $platinumFeatures = strtolower(implode(' ', $platinum['features']));
        $this->assertStringContainsString('transaksi', $platinumFeatures);
        $this->assertStringContainsString('diskon', $platinumFeatures);
    }
}
