<?php

namespace Tests\Feature\Landing;

use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingNavbarStandardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_landing_navbar_renders_as_slim_full_width_navbar_with_locale_and_dark_mode(): void
    {
        $response = $this->get(route('customer.home'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Header full-width, transparan di posisi atas
        $this->assertStringContainsString('id="floating-header"', $content);
        $this->assertMatchesRegularExpression(
            '/<header[^>]*id="floating-header"[^>]*class="[^"]*fixed top-0 inset-x-0 z-50 w-full[^"]*bg-transparent[^"]*"/',
            $content
        );

        // 2. Konten berada dalam container standar max-w-7xl dengan tinggi ramping h-14 sm:h-16
        $this->assertMatchesRegularExpression(
            '/<div[^>]*id="navbar-bar"[^>]*class="[^"]*max-w-7xl mx-auto px-4 sm:px-6 lg:px-8[^"]*"/',
            $content
        );
        $this->assertStringContainsString('h-14 sm:h-16', $content);

        // 3. Yang harus dihilangkan (bukan floating pill di tengah, tidak ada backdrop blur)
        $this->assertStringNotContainsString('max-w-5xl mx-auto mt-2', $content);
        $this->assertStringNotContainsString('backdrop-blur-md', $content);

        // 4. Logo SIMAUL berwarna putih di atas (brightness-0 invert) dan proporsional (h-7 sm:h-8)
        $this->assertMatchesRegularExpression(
            '/<img[^>]*id="brand-logo-img"[^>]*class="[^"]*h-7 sm:h-8[^"]*brightness-0 invert[^"]*"/',
            $content
        );

        // 5. Tombol bahasa ID 🇮🇩 tepat di sebelah kiri tombol Masuk (tanpa tombol Dark Mode di landing page)
        $this->assertStringContainsString('id="btn-switch-locale"', $content);
        $this->assertStringContainsString('ID 🇮🇩', $content);
        $this->assertStringNotContainsString('id="theme-toggle-btn"', $content);
        $this->assertStringNotContainsString('id="theme-moon-icon"', $content);
        $this->assertStringNotContainsString('id="theme-sun-icon"', $content);

        // Posisi berurutan: btn-switch-locale < header-login-btn
        $posLocale = strpos($content, 'id="btn-switch-locale"');
        $posLogin = strpos($content, 'id="header-login-btn"');
        $this->assertTrue($posLocale !== false && $posLogin !== false, 'Elemen aksi navbar harus ditemukan');
        $this->assertTrue($posLocale < $posLogin, 'Tombol bahasa harus berada tepat di kiri tombol Masuk');

        // 6. Tombol Masuk
        $this->assertStringContainsString('Masuk', $content);
    }

    public function test_locale_toggle_switches_between_indonesian_and_english(): void
    {
        // 1. Default request Bahasa Indonesia
        $responseId = $this->get(route('customer.home'));
        $responseId->assertStatus(200);
        $responseId->assertSee('ID 🇮🇩');
        $responseId->assertSee('Masuk');
        $responseId->assertSee('BAGAIMANA JIKA');

        // 2. Toggle ke English via POST
        $toggleRes = $this->postJson(route('locale.toggle'));
        $toggleRes->assertStatus(200);
        $toggleRes->assertJson(['success' => true, 'locale' => 'en', 'label' => 'EN 🇬🇧']);

        // 3. Akses kembali dengan session 'en'
        $responseEn = $this->withSession(['locale' => 'en'])->get(route('customer.home'));
        $responseEn->assertStatus(200);
        $responseEn->assertSee('EN 🇬🇧');
        $responseEn->assertSee('Sign In');
        $responseEn->assertSee('WHAT IF');
        $responseEn->assertSee('Choose Branch');

        // 4. Toggle kembali ke Bahasa Indonesia
        $toggleBack = $this->withSession(['locale' => 'en'])->postJson(route('locale.toggle'));
        $toggleBack->assertStatus(200);
        $toggleBack->assertJson(['success' => true, 'locale' => 'id', 'label' => 'ID 🇮🇩']);
    }

    public function test_customer_area_navbar_renders_with_solid_white_full_width_background(): void
    {
        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Pelanggan Test',
            'email' => 'pelanggan@test.com',
        ]);

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // 1. Header full-width putih solid
        $this->assertMatchesRegularExpression(
            '/<header[^>]*id="floating-header"[^>]*class="[^"]*fixed top-0 inset-x-0 z-50 w-full[^"]*bg-white border-b border-\[#E4E7EC\][^"]*"/',
            $content
        );

        // 2. Container max-w-7xl h-14 sm:h-16
        $this->assertMatchesRegularExpression(
            '/<div[^>]*id="navbar-bar"[^>]*class="[^"]*max-w-7xl mx-auto px-4 sm:px-6 lg:px-8[^"]*"/',
            $content
        );
        $this->assertStringContainsString('h-14 sm:h-16', $content);

        // 3. Logo warna biru (tanpa invert)
        $this->assertDoesNotMatchRegularExpression(
            '/<img[^>]*id="brand-logo-img"[^>]*class="[^"]*brightness-0 invert[^"]*"/',
            $content
        );

        // 4. Tombol profil hanya menampilkan avatar lingkaran tanpa teks nama akun
        $this->assertStringContainsString('id="customer-profile-btn"', $content);
        $this->assertMatchesRegularExpression(
            '/<button[^>]*id="customer-profile-btn"[^>]*class="[^"]*rounded-full[^"]*"/',
            $content
        );

        // Pastikan nama akun tidak ada di dalam button profil
        preg_match('/<button[^>]*id="customer-profile-btn"[^>]*>(.*?)<\/button>/s', $content, $btnMatch);
        $this->assertNotEmpty($btnMatch);
        $this->assertStringNotContainsString('Pelanggan Test', $btnMatch[1], 'Nama akun tidak boleh muncul di tombol navbar');

        // Pastikan nama akun tetap tersedia di dalam dropdown
        $this->assertStringContainsString('id="customer-profile-dropdown"', $content);
        preg_match('/<div[^>]*id="customer-profile-dropdown"[^>]*>(.*?)<\/div>/s', $content, $dropdownMatch);
        $this->assertNotEmpty($dropdownMatch);
        $this->assertStringContainsString('Pelanggan Test', $dropdownMatch[1], 'Nama akun harus tetap tampil di dropdown menu profil');
    }

    public function test_landing_navbar_menu_order_and_review_link(): void
    {
        $branch = Branch::create([
            'name' => 'SIMAUL Pusat',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '0812-9999-8888',
            'service_mode' => 'appointment',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'operating_days' => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
            'max_slot_capacity' => 5,
            'is_active' => true,
        ]);
        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);
        $category = ServiceCategory::create(['name' => 'Barbershop']);
        $service = Service::create([
            'branch_id' => $branch->id,
            'service_category_id' => $category->id,
            'name' => 'Gentleman Cut',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
        $trx = Transaction::create([
            'transaction_code' => 'TRX-TEST-999',
            'cashier_id' => $user->id,
            'branch_id' => $branch->id,
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'transaction_date' => now(),
            'status' => StatusTransaksi::Paid,
        ]);
        Ulasan::create([
            'user_id' => $user->id,
            'transaction_id' => $trx->id,
            'service_id' => $service->id,
            'rating' => 5,
            'komentar' => 'Potongan rapi sekali!',
        ]);

        $responseId = $this->get(route('customer.home'));
        $responseId->assertOk();
        $contentId = $responseId->getContent();

        // 1. Menu order in Indonesian within <nav>: Beranda -> Cara Kerja -> Cabang -> Layanan -> Promo -> Ulasan
        preg_match('/<nav class="hidden lg:flex items-center gap-1">(.*?)<\/nav>/s', $contentId, $navMatch);
        $this->assertNotEmpty($navMatch);
        $navHtml = $navMatch[1];

        $posHome = strpos($navHtml, 'Beranda');
        $posWorkflow = strpos($navHtml, 'Cara Kerja');
        $posBranches = strpos($navHtml, 'Cabang');
        $posServices = strpos($navHtml, 'Layanan');
        $posPromo = strpos($navHtml, 'Promo');
        $posReviews = strpos($navHtml, 'Ulasan');

        $this->assertTrue($posHome !== false && $posWorkflow !== false && $posBranches !== false && $posServices !== false && $posPromo !== false && $posReviews !== false);
        $this->assertTrue($posHome < $posWorkflow, 'Beranda before Cara Kerja');
        $this->assertTrue($posWorkflow < $posBranches, 'Cara Kerja before Cabang');
        $this->assertTrue($posBranches < $posServices, 'Cabang before Layanan');
        $this->assertTrue($posServices < $posPromo, 'Layanan before Promo');
        $this->assertTrue($posPromo < $posReviews, 'Promo before Ulasan');

        // 2. Menu targets: #cara-kerja, #cabang, #layanan, #promo, #ulasan
        $this->assertStringContainsString('href="#cara-kerja"', $navHtml);
        $this->assertStringContainsString('href="#cabang"', $navHtml);
        $this->assertStringContainsString('href="#layanan"', $navHtml);
        $this->assertStringContainsString('href="#promo"', $navHtml);
        $this->assertStringContainsString('href="#ulasan"', $navHtml);
        $this->assertStringNotContainsString('href="#tentang"', $navHtml);

        // 3. English translation check within <nav>
        $responseEn = $this->withSession(['locale' => 'en'])->get(route('customer.home'));
        $responseEn->assertOk();
        $contentEn = $responseEn->getContent();

        preg_match('/<nav class="hidden lg:flex items-center gap-1">(.*?)<\/nav>/s', $contentEn, $navEnMatch);
        $this->assertNotEmpty($navEnMatch);
        $navEnHtml = $navEnMatch[1];

        $posEnHome = strpos($navEnHtml, 'Home');
        $posEnWorkflow = strpos($navEnHtml, 'How It Works');
        $posEnBranches = strpos($navEnHtml, 'Branches');
        $posEnServices = strpos($navEnHtml, 'Services');
        $posEnPromo = strpos($navEnHtml, 'Promo');
        $posEnReviews = strpos($navEnHtml, 'Reviews');

        $this->assertTrue($posEnHome !== false && $posEnWorkflow !== false && $posEnBranches !== false && $posEnServices !== false && $posEnPromo !== false && $posEnReviews !== false);
        $this->assertTrue($posEnHome < $posEnWorkflow);
        $this->assertTrue($posEnWorkflow < $posEnBranches);
        $this->assertTrue($posEnBranches < $posEnServices);
        $this->assertTrue($posEnServices < $posEnPromo);
        $this->assertTrue($posEnPromo < $posEnReviews);

        // 4. Mobile dropdown nav order check
        preg_match('/<div id="mobile-nav-panel"[^>]*>(.*?)<\/div>/s', $contentId, $mobileMatch);
        $this->assertNotEmpty($mobileMatch);
        $mobileHtml = $mobileMatch[1];
        $this->assertStringContainsString('href="#cara-kerja"', $mobileHtml);
        $this->assertStringContainsString('href="#cabang"', $mobileHtml);
        $this->assertStringContainsString('href="#layanan"', $mobileHtml);
        $this->assertStringContainsString('href="#promo"', $mobileHtml);
        $this->assertStringContainsString('href="#ulasan"', $mobileHtml);
        $this->assertStringNotContainsString('href="#tentang"', $mobileHtml);

        // 5. Testimonial text size is 13px mobile, 15px desktop
        $this->assertStringContainsString('text-[13px] sm:text-[15px]', $contentId);
    }
}
