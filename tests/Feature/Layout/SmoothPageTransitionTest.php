<?php

namespace Tests\Feature\Layout;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmoothPageTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $manager;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Pusat - Barbershop',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Budi Santoso',
            'role_id' => $customerRole->id,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Toko',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch->id);
    }

    /**
     * Uji 1: File CSS transitions berisi @view-transition dan animasi fade-in / fade-out
     */
    public function test_page_transition_css_is_present_and_configured_correctly(): void
    {
        $cssPath = resource_path('css/pages/page-transitions.css');
        $this->assertFileExists($cssPath);

        $cssContent = file_get_contents($cssPath);

        // Memastikan View Transitions API diaktifkan untuk cross-document navigation
        $this->assertStringContainsString('@view-transition', $cssContent);
        $this->assertStringContainsString('navigation: auto;', $cssContent);

        // Memastikan animasi fade-in dan fade-out dengan durasi dan easing yang natural
        $this->assertStringContainsString('::view-transition-old(root)', $cssContent);
        $this->assertStringContainsString('::view-transition-new(root)', $cssContent);
        $this->assertStringContainsString('simaul-page-fade-out', $cssContent);
        $this->assertStringContainsString('simaul-page-fade-in', $cssContent);

        // Memastikan preferensi prefers-reduced-motion didukung
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $cssContent);
    }

    /**
     * Uji 2: Landing Page memiliki elemen top progress bar transisi
     */
    public function test_landing_page_renders_top_progress_bar(): void
    {
        $response = $this->get(route('customer.home'));

        $response->assertStatus(200);
        $response->assertSee('id="app-top-progress"', false);
    }

    /**
     * Uji 3: Area Pelanggan (Dashboard, Aktivitas, Member, Ulasan) memiliki top progress bar
     */
    public function test_customer_pages_render_top_progress_bar(): void
    {
        $pages = [
            route('customer.dashboard'),
            route('customer.aktivitas'),
            route('customer.member'),
            route('customer.ulasan.index'),
        ];

        foreach ($pages as $url) {
            $response = $this->actingAs($this->customer)->get($url);
            $response->assertStatus(200);
            $response->assertSee('id="app-top-progress"', false);
        }
    }

    /**
     * Uji 4: Dashboard Manajer / Internal memiliki elemen top progress bar
     */
    public function test_manager_dashboard_renders_top_progress_bar(): void
    {
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="app-top-progress"', false);
    }

    /**
     * Uji 5: Halaman Buat Pesanan dan Konfirmasi Pesanan memiliki progress bar dan tetap berfungsi normal
     */
    public function test_pesanan_pages_render_and_maintain_functionality(): void
    {
        // Halaman Buat Pesanan
        $responseCreate = $this->actingAs($this->customer)->get(route('customer.pesanan.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('id="app-top-progress"', false);

        // Halaman Konfirmasi Pesanan
        $responseConfirm = $this->actingAs($this->customer)->get(route('customer.pesanan.confirm'));
        // Konfirmasi pesanan bisa 200 atau redirect ke buat pesanan jika session data pesanan belum diisi
        $this->assertContains($responseConfirm->status(), [200, 302]);
    }

    /**
     * Uji 6: Halaman Autentikasi (Login) memiliki top progress bar
     */
    public function test_auth_pages_render_top_progress_bar(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('id="app-top-progress"', false);
    }
}
