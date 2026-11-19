<?php

namespace Tests\Feature;

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

class NavbarSpacingAndLocaleTest extends TestCase
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

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Ahmad Pelanggan',
            'email' => 'ahmad@example.com',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'name' => 'Budi Manajer',
            'email' => 'manager@simaul.com',
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $category = ServiceCategory::create(['name' => 'Barbershop']);
        $service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Gentleman Cut',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Kasir Demo',
            'email' => 'kasir@simaul.com',
        ]);

        $trx = Transaction::create([
            'transaction_code' => 'TRX-TEST-001',
            'cashier_id' => $cashier->id,
            'branch_id' => $this->branch->id,
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'transaction_date' => now(),
            'status' => StatusTransaksi::Paid,
        ]);

        Ulasan::create([
            'user_id' => $this->customer->id,
            'transaction_id' => $trx->id,
            'service_id' => $service->id,
            'rating' => 5,
            'komentar' => 'Pelayanan ramah dan potongan sangat rapi.',
        ]);
    }

    public function test_navbar_spacing_and_dimensions_match_strict_rules(): void
    {
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $response->assertOk();
        $content = $response->getContent();

        // 1. Container flex dengan gap konsisten 8–10px (gap-2 sm:gap-2.5)
        $this->assertMatchesRegularExpression(
            '/class="[^"]*flex items-center gap-2 sm:gap-2\.5 shrink-0[^"]*"/',
            $content
        );

        // 2. Tombol bahasa tinggi 36px (h-9)
        $this->assertMatchesRegularExpression(
            '/<button[^>]*class="[^"]*h-9[^"]*"[^>]*id="btn-switch-locale"|<button[^>]*id="btn-switch-locale"[^>]*class="[^"]*h-9[^"]*"/',
            $content
        );

        // 3. Area tombol notifikasi 36x36px (w-9 h-9)
        $this->assertMatchesRegularExpression(
            '/<button[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"[^>]*id="notification-bell-btn"|<button[^>]*id="notification-bell-btn"[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"/',
            $content
        );

        // 4. Ikon lonceng 20px (w-5 h-5) dengan warna navy
        $this->assertMatchesRegularExpression(
            '/<svg[^>]*class="[^"]*w-5 h-5 text-\[#062072\] dark:text-\[#93C5FD\][^"]*"/',
            $content
        );

        // 5. Tombol Dark Mode 36x36px, lingkaran sempurna (w-9 h-9 rounded-full) dengan ikon 19px
        $this->assertMatchesRegularExpression(
            '/<button[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"[^>]*id="internal-theme-toggle-btn"|<button[^>]*id="internal-theme-toggle-btn"[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"/',
            $content
        );
        $this->assertStringContainsString('w-[19px] h-[19px]', $content);

        // 6. Tombol Foto Profil 36x36px, lingkaran sempurna (w-9 h-9 rounded-full)
        $this->assertMatchesRegularExpression(
            '/<button[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"[^>]*id="header-profile-btn"|<button[^>]*id="header-profile-btn"[^>]*class="[^"]*w-9 h-9 rounded-full[^"]*"/',
            $content
        );
    }

    public function test_switching_locale_to_english_translates_interface_consistently(): void
    {
        // 1. Landing Page ID vs EN
        $landingId = $this->withSession(['locale' => 'id'])->get(route('customer.home'));
        $landingId->assertOk();
        $landingId->assertSee('ID 🇮🇩');
        $landingId->assertSee('Cabang Layanan');
        $landingId->assertSee('Jam Operasional');
        $landingId->assertSee('Senin – Sabtu (Minggu libur)');
        $landingId->assertSee('Pilih Cabang');
        $landingId->assertSee('Apa Kata Mereka?');
        $landingId->assertSee('Layanan Terpadu Terstandar');
        $landingId->assertSee('Privasi');
        $landingId->assertSee('Syarat & Ketentuan');

        $landingEn = $this->withSession(['locale' => 'en'])->get(route('customer.home'));
        $landingEn->assertOk();
        $landingEn->assertSee('EN 🇬🇧');
        $landingEn->assertSee('Service Branches');
        $landingEn->assertSee('Operating Hours');
        $landingEn->assertSee('Monday – Saturday (Closed on Sundays)');
        $landingEn->assertSee('Select Branch');
        $landingEn->assertSee('What They Say');
        $landingEn->assertSee('Standardized Integrated Services');
        $landingEn->assertSee('Privacy Policy');
        $landingEn->assertSee('Terms & Conditions');

        // 2. Notification Bell Dropdown ID vs EN
        $dashboardId = $this->actingAs($this->manager)->withSession(['locale' => 'id'])->get(route('manager.dashboard'));
        $dashboardId->assertOk();
        $dashboardId->assertSee('Tandai semua dibaca');
        $dashboardId->assertSee('Tidak ada notifikasi baru');

        $dashboardEn = $this->actingAs($this->manager)->withSession(['locale' => 'en'])->get(route('manager.dashboard'));
        $dashboardEn->assertOk();
        $dashboardEn->assertSee('Mark all as read');
        $dashboardEn->assertSee('No new notifications');

        // 3. Customer Dashboard ID vs EN
        $custDashboardId = $this->actingAs($this->customer)->withSession(['locale' => 'id'])->get(route('customer.dashboard'));
        $custDashboardId->assertOk();
        $custDashboardId->assertSee('Halo, Ahmad Pelanggan!');
        $custDashboardId->assertSee('Pilih Layanan');
        $custDashboardId->assertSee('Aktivitas Saya');
        $custDashboardId->assertSee('Member &amp; Poin', false);

        $custDashboardEn = $this->actingAs($this->customer)->withSession(['locale' => 'en'])->get(route('customer.dashboard'));
        $custDashboardEn->assertOk();
        $custDashboardEn->assertSee('Hello, Ahmad Pelanggan!');
        $custDashboardEn->assertSee('Choose Service');
        $custDashboardEn->assertSee('My Activities');
        $custDashboardEn->assertSee('Member &amp; Points', false);

        // 4. Manager Dashboard ID vs EN
        $mgrId = $this->actingAs($this->manager)->withSession(['locale' => 'id'])->get(route('manager.dashboard'));
        $mgrId->assertOk();
        $mgrId->assertSee('Dashboard Manajer');
        $mgrId->assertSee('Omzet Cabang Hari Ini');
        $mgrId->assertSee('Jumlah Transaksi');
        $mgrId->assertSee('Karyawan Bertugas');
        $mgrId->assertSee('Semua Transaksi Cabang');

        $mgrEn = $this->actingAs($this->manager)->withSession(['locale' => 'en'])->get(route('manager.dashboard'));
        $mgrEn->assertOk();
        $mgrEn->assertSee('Manager Dashboard');
        $mgrEn->assertSee('Today&#039;s Branch Turnover', false);
        $mgrEn->assertSee('Total Transactions');
        $mgrEn->assertSee('Employees on Duty');
        $mgrEn->assertSee('All Branch Transactions');

        // 5. Owner Dashboard ID vs EN
        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Pak Owner',
            'email' => 'owner@simaul.com',
        ]);
        $ownerId = $this->actingAs($owner)->withSession(['locale' => 'id'])->get(route('owner.dashboard'));
        $ownerId->assertOk();
        $ownerId->assertSee('Dashboard Owner');
        $ownerId->assertSee('Total Cabang Aktif');
        $ownerId->assertSee('Performa Tiap Cabang');
        $ownerId->assertSee('Grafik Tren Omzet 14 Hari Terakhir');
        $ownerId->assertSee('Efektivitas Promo &amp; Kupon', false);
        $ownerId->assertSee('Semua Transaksi');

        $ownerEn = $this->actingAs($owner)->withSession(['locale' => 'en'])->get(route('owner.dashboard'));
        $ownerEn->assertOk();
        $ownerEn->assertSee('Owner Dashboard');
        $ownerEn->assertSee('Total Active Branches');
        $ownerEn->assertSee('Branch Performance');
        $ownerEn->assertSee('Turnover Trend (Last 14 Days)');
        $ownerEn->assertSee('Promo &amp; Coupon Effectiveness', false);
        $ownerEn->assertSee('All Transactions');

        // 6. Cashier Dashboard ID vs EN
        $cashierRole = Role::where('name', UserRole::Cashier->value)->firstOrFail();
        $cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'name' => 'Siti Kasir',
            'email' => 'siti.kasir@simaul.com',
        ]);
        $cashier->branches()->attach($this->branch->id);

        $cashierId = $this->actingAs($cashier)->withSession(['locale' => 'id'])->get(route('cashier.dashboard'));
        $cashierId->assertOk();
        $cashierId->assertSee('Dashboard Kasir');
        $cashierId->assertSee('Pesanan yang Perlu Ditangani');
        $cashierId->assertSee('Status Shift');
        $cashierId->assertSee('Kas Tunai di Laci');

        $cashierEn = $this->actingAs($cashier)->withSession(['locale' => 'en'])->get(route('cashier.dashboard'));
        $cashierEn->assertOk();
        $cashierEn->assertSee('Cashier Dashboard');
        $cashierEn->assertSee('Orders to Handle');
        $cashierEn->assertSee('Shift Status');
        $cashierEn->assertSee('Cash in Drawer');

        // 7. Employee Dashboard ID vs EN
        $employeeRole = Role::where('name', UserRole::Employee->value)->firstOrFail();
        $employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Doni Staf',
            'email' => 'doni.staf@simaul.com',
        ]);
        $employeeUser->branches()->attach($this->branch->id);

        $empId = $this->actingAs($employeeUser)->withSession(['locale' => 'id'])->get(route('employee.dashboard'));
        $empId->assertOk();
        $empId->assertSee('Dashboard Karyawan');
        $empId->assertSee('Jadwal Shift Saya');
        $empId->assertSee('Estimasi &amp; Rekap Komisi', false);

        $empEn = $this->actingAs($employeeUser)->withSession(['locale' => 'en'])->get(route('employee.dashboard'));
        $empEn->assertOk();
        $empEn->assertSee('Employee Dashboard');
        $empEn->assertSee('My Shifts');
        $empEn->assertSee('Commission Estimates &amp; Summary', false);
    }
}
