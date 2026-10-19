<?php

namespace Tests\Feature\Kasir;

use App\Enums\CashierShiftStatus;
use App\Models\CashierShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirHalamanShiftDanNavigasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('simaul:setup-demo-kasir');
    }

    public function test_halaman_shift_kasir_dapat_diakses_secara_tersendiri_dengan_judul_benar(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $this->assertNotNull($kasir);

        $response = $this->actingAs($kasir)->get(route('pos.shift.index'));

        $response->assertStatus(200);
        $response->assertSee('Shift Kasir | SIMAUL');
        $response->assertSee('Shift Kasir');
        $response->assertSee('Barbershop', false);
        $response->assertSee('Shift Aktif');
        $response->assertSee('Rekapitulasi Penjualan', false);
        $response->assertSee('Tutup Sesi Shift Kasir');
        $response->assertSee('Riwayat Sesi Shift Kasir');
    }

    public function test_halaman_dashboard_kasir_berfokus_pada_ringkasan_operasional(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        $response = $this->actingAs($kasir)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Kasir | SIMAUL');
        $response->assertSee('Dashboard Kasir');
        $response->assertSee('Status Shift');
        $response->assertSee('Transaksi Hari Ini');
        $response->assertSee('Penjualan Shift');
        $response->assertSee('Kas Tunai di Laci');
        $response->assertSee(route('pos.shift.index'));
        $response->assertSee('Bayar di POS');

        // Pastikan tombol Buka Terminal POS dan 4 kartu shortcut tidak ada di Dashboard
        $response->assertDontSee('Buka Terminal POS');
        $response->assertDontSee('Buat transaksi baru');
        $response->assertDontSee('Lihat & cetak ulang');
        $response->assertDontSee('Kelola sesi & kas laci');
        $response->assertDontSee('Data & loyalitas member');

        // Verifikasi tipografi kartu statistik 20px font-semibold
        $response->assertSee('text-[20px]', false);
        $response->assertSee('font-semibold', false);
    }

    public function test_terminal_pos_menampilkan_indikator_ringkas_tanpa_tombol_tutup_shift_ganda(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        $response = $this->actingAs($kasir)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('Terminal POS | SIMAUL');
        $response->assertSee('Terminal POS');
        $response->assertSee('Kelola Shift Kasir');
        $response->assertSee(route('pos.shift.index'));
        // Tidak ada form tutup shift modal di terminal POS
        $response->assertDontSee('modal-close-shift');
    }

    public function test_sidebar_kasir_memakai_logo_header_dan_sidebar_putih(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        $response = $this->actingAs($kasir)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('images/brand/logo-header.png');
        $response->assertDontSee('images/brand/logo-sidebar.png');
        $response->assertSee('header-realtime-clock');
        $response->assertSee('Kasir');
    }

    public function test_buka_dan_tutup_shift_dari_halaman_shift_dapat_redirect_kembali_ke_shift(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $activeShift = CashierShift::where('user_id', $kasir->id)
            ->where('status', CashierShiftStatus::Open)
            ->first();

        // Tutup shift dengan redirect_to halaman shift
        $responseClose = $this->actingAs($kasir)->post(route('pos.shift.close'), [
            'branch_id' => 1,
            'closing_cash' => 280000,
            'notes' => 'Tutup shift dari halaman khusus shift',
            'redirect_to' => route('pos.shift.index'),
        ]);

        $responseClose->assertRedirect(route('pos.shift.index'));
        $this->assertEquals(CashierShiftStatus::Closed, $activeShift->fresh()->status);

        // Buka kembali shift dengan redirect_to halaman shift
        $responseOpen = $this->actingAs($kasir)->post(route('pos.shift.open'), [
            'branch_id' => 1,
            'opening_cash' => 250000,
            'redirect_to' => route('pos.shift.index'),
        ]);

        $responseOpen->assertRedirect(route('pos.shift.index'));
        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $kasir->id,
            'branch_id' => 1,
            'status' => CashierShiftStatus::Open->value,
            'opening_cash' => 250000,
        ]);
    }

    public function test_judul_tab_browser_dan_heading_konsisten(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        // Dashboard
        $this->actingAs($kasir)->get(route('cashier.dashboard'))
            ->assertSee('Dashboard Kasir | SIMAUL')
            ->assertSee('Dashboard Kasir');

        // POS
        $this->actingAs($kasir)->get(route('pos.index'))
            ->assertSee('Terminal POS | SIMAUL')
            ->assertSee('Terminal POS');

        // Shift
        $this->actingAs($kasir)->get(route('pos.shift.index'))
            ->assertSee('Shift Kasir | SIMAUL')
            ->assertSee('Shift Kasir');

        // Transaksi
        $this->actingAs($kasir)->get(route('pos.transactions.index'))
            ->assertSee('Riwayat Transaksi | SIMAUL')
            ->assertSee('Riwayat Transaksi');

        // Member
        $this->actingAs($kasir)->get(route('members.index'))
            ->assertSee('Member | SIMAUL')
            ->assertSee('Member');
    }
}
