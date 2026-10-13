<?php

namespace Tests\Feature\Landing;

use App\Models\Branch;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontenLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_landing_page_renders_universal_simaul_headline_and_five_step_workflow(): void
    {
        $response = $this->get(route('customer.home'));

        $response->assertStatus(200);
        $response->assertSee('BAGAIMANA JIKA');
        $response->assertSee('SEMUA LAYANAN');
        $response->assertSee('JADI LEBIH');
        $response->assertSee('MUDAH?');
        $response->assertSee('Jelajahi Layanan');
        $response->assertSee('Pilih Cabang');

        $response->assertSee('01');
        $response->assertSee('Pilih Cabang');
        $response->assertSee('02');
        $response->assertSee('Pilih Layanan');
        $response->assertSee('03');
        $response->assertSee('Buat Pesanan');
        $response->assertSee('04');
        $response->assertSee('Pantau Proses');
        $response->assertSee('05');
        $response->assertSee('Selesaikan Pembayaran');
    }

    public function test_landing_page_shows_all_branches_and_operational_mode_labels(): void
    {
        $branchAppointment = Branch::create([
            'name' => 'SIMAUL Studio Barber Test',
            'address' => 'Jl. Barber Test No. 1',
            'phone' => '021-999901',
            'service_mode' => 'appointment',
            'is_active' => true,
        ]);

        $branchDropOff = Branch::create([
            'name' => 'SIMAUL Drop Off Test',
            'address' => 'Jl. Drop Test No. 2',
            'phone' => '021-999902',
            'service_mode' => 'drop_off',
            'is_active' => true,
        ]);

        $branchBoth = Branch::create([
            'name' => 'SIMAUL Hybrid Studio Test',
            'address' => 'Jl. Hybrid Test No. 3',
            'phone' => '021-999903',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $response = $this->get(route('customer.home'));

        $response->assertStatus(200);
        $response->assertSee($branchAppointment->display_name);
        $response->assertSee($branchDropOff->display_name);
        $response->assertSee($branchBoth->display_name);
        $response->assertSee('Appointment');
        $response->assertSee('Drop-off');
        $response->assertSee('Appointment & Drop-off');
    }
}
