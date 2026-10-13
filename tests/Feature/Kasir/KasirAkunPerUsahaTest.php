<?php

namespace Tests\Feature\Kasir;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirAkunPerUsahaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('simaul:setup-demo-kasir');
    }

    public function test_tiga_akun_kasir_demo_dapat_login_dan_mengakses_dashboard_kasir(): void
    {
        $accounts = [
            'kasir@simaul.com',
            'kasir.laundry@simaul.com',
            'kasir.autocare@simaul.com',
        ];

        $plainPassword = (string) (env('DEMO_CASHIER_PASSWORD') ?: 'password123');

        foreach ($accounts as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Akun kasir {$email} harus terdaftar di database.");
            $this->assertEquals('cashier', $user->role->name);

            $response = $this->post(route('login'), [
                'email' => $email,
                'password' => $plainPassword,
            ]);

            $response->assertRedirect(route('cashier.dashboard'));
            $this->assertAuthenticatedAs($user);

            $dashResponse = $this->actingAs($user)->get(route('cashier.dashboard'));
            $dashResponse->assertOk();
            $dashResponse->assertSee('Terminal POS');

            $this->post(route('logout'));
        }
    }

    public function test_kasir_terhubung_ke_usaha_yang_benar_dan_hanya_melihat_cabang_miliknya(): void
    {
        $kasirBarber = User::where('email', 'kasir@simaul.com')->first();
        $kasirLaundry = User::where('email', 'kasir.laundry@simaul.com')->first();
        $kasirAuto = User::where('email', 'kasir.autocare@simaul.com')->first();

        $this->assertEquals(1, $kasirBarber->branches()->first()->id);
        $this->assertEquals(2, $kasirLaundry->branches()->first()->id);
        $this->assertEquals(3, $kasirAuto->branches()->first()->id);

        $respBarber = $this->actingAs($kasirBarber)->get(route('pos.index'));
        $respBarber->assertOk();
        $respBarber->assertSee('Barbershop & Studio');
        $respBarber->assertDontSee('Laundry & Shoe Care');
    }

    public function test_kasir_satu_usaha_tidak_dapat_mengakses_detail_atau_struk_transaksi_usaha_lain(): void
    {
        $kasirBarber = User::where('email', 'kasir@simaul.com')->first();
        $trxLaundry = Transaction::where('transaction_code', 'TRX-DEMO-LAUNDRY-01')->first();

        $this->assertNotNull($trxLaundry);

        // Kasir Barbershop mencoba mengakses transaksi cabang Laundry (Cabang 2)
        $responseShow = $this->actingAs($kasirBarber)->get(route('pos.transactions.show', $trxLaundry));
        $responseShow->assertForbidden();

        $responseReceipt = $this->actingAs($kasirBarber)->get(route('pos.transactions.receipt', $trxLaundry));
        $responseReceipt->assertForbidden();
    }

    public function test_kasir_tidak_memiliki_akses_ke_fitur_administratif_owner_dan_manager(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        // Tidak boleh akses dashboard owner atau manager
        $this->actingAs($kasir)->get(route('owner.dashboard'))->assertForbidden();
        $this->actingAs($kasir)->get(route('manager.dashboard'))->assertForbidden();

        // Tidak boleh akses kelola user internal owner
        $this->actingAs($kasir)->get(route('owner.internal-users.index'))->assertForbidden();

        // Tidak boleh akses create promo (khusus owner/manager)
        $this->actingAs($kasir)->get(route('promotions.index'))->assertForbidden();
        $this->actingAs($kasir)->get(route('promotions.create'))->assertForbidden();
    }
}
