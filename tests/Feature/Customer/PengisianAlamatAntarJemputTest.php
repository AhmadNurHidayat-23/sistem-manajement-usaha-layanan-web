<?php

namespace Tests\Feature\Customer;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PengisianAlamatAntarJemputTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Pelanggan',
            'email' => 'budi.pelanggan@example.com',
            'address' => 'Jl. Merdeka No. 10',
        ]);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Cabang Tebet',
            'address' => 'Jl. Tebet Raya No. 5',
            'phone' => '081234567890',
            'service_mode' => 'both',
            'is_active' => true,
        ]);

        $cat = ServiceCategory::create(['name' => 'Laundry Kiloan']);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $cat->id,
            'name' => 'Cuci Komplit Express',
            'price' => 12000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'pricing_type' => 'per_kg',
            'minimum_weight' => 2,
            'is_active' => true,
        ]);
    }

    /**
     * Halaman form pesanan memuat elemen reverse geocoding, peta Leaflet, dan indikator status alamat.
     */
    public function test_halaman_pesanan_merender_elemen_geocoding_dan_peta(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.pesanan.create', [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
        ]));

        $response->assertOk();
        $response->assertSee('id="pickup-location-section"', false);
        $response->assertSee('id="pickup_address"', false);
        $response->assertSee('id="pickup-address-status"', false);
        $response->assertSee('id="btn-gps-pickup"', false);
        $response->assertSee('id="pickup-map"', false);
        $response->assertSee('id="pickup_latitude"', false);
        $response->assertSee('id="pickup_longitude"', false);

        // Elemen pengantaran berbeda
        $response->assertSee('id="different_delivery_location"', false);
        $response->assertSee('id="delivery_address"', false);
        $response->assertSee('id="delivery-address-status"', false);
        $response->assertSee('id="btn-gps-delivery"', false);
        $response->assertSee('id="delivery-map"', false);
    }

    /**
     * Validasi mewajibkan alamat penjemputan saat layanan antar-jemput aktif.
     */
    public function test_validasi_menolak_jika_alamat_penjemputan_kosong(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'estimated_quantity' => 3,
            'pickup_option' => 1,
            'pickup_address' => '',
            'pickup_latitude' => -6.2255,
            'pickup_longitude' => 106.8556,
        ]);

        $response->assertSessionHasErrors('pickup_address');
    }

    /**
     * Alamat dan koordinat yang terisi (baik otomatis dari peta maupun editan manual) lolos validasi konfirmasi.
     */
    public function test_alamat_dan_koordinat_hasil_peta_dan_editan_manual_diterima(): void
    {
        $alamatLengkap = 'Jalan Sultan Agung No. 15, RT 03/RW 05, Setiabudi, Jakarta Selatan, 12850';

        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'estimated_quantity' => 3.5,
            'pickup_option' => 1,
            'pickup_address' => $alamatLengkap,
            'pickup_latitude' => -6.2088,
            'pickup_longitude' => 106.8456,
            'location_notes' => 'Pagar putih, depan minimarket',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertOk();
        $response->assertSee('Konfirmasi Pesanan');
        $response->assertSee($alamatLengkap);
        $response->assertSee('Pagar putih, depan minimarket');
    }

    /**
     * Alamat pengantaran berbeda juga tervalidasi dan tersimpan dengan koordinatnya.
     */
    public function test_alamat_dan_koordinat_pengantaran_berbeda_tersimpan(): void
    {
        $alamatJemput = 'Jalan Tebet Timur Raya No. 8, Jakarta Selatan';
        $alamatAntar = 'Jalan Casablanca Kav. 88, Menteng Dalam, Jakarta Selatan';

        $response = $this->actingAs($this->customer)->post(route('customer.pesanan.confirm'), [
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'estimated_quantity' => 4,
            'pickup_option' => 1,
            'pickup_address' => $alamatJemput,
            'pickup_latitude' => -6.2300,
            'pickup_longitude' => 106.8500,
            'different_delivery_location' => 1,
            'delivery_address' => $alamatAntar,
            'delivery_latitude' => -6.2230,
            'delivery_longitude' => 106.8420,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertOk();
        $response->assertSee($alamatJemput);
        $response->assertSee($alamatAntar);
    }

    /**
     * Endpoint reverse geocode proxy mengembalikan data alamat terformat.
     */
    public function test_endpoint_reverse_geocode_mengembalikan_alamat_dari_koordinat(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'display_name' => 'Jalan Sultan Agung, Pasar Manggis, Jakarta Selatan, 12850',
                'address' => [
                    'road' => 'Jalan Sultan Agung',
                    'house_number' => '15',
                    'village' => 'Pasar Manggis',
                    'city_district' => 'Setiabudi',
                    'city' => 'Jakarta Selatan',
                    'postcode' => '12850',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->customer)->getJson(route('customer.pesanan.reverse-geocode', [
            'lat' => -6.2088,
            'lng' => 106.8456,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('Jalan Sultan Agung', $response->json('data.address.road'));
    }

    /**
     * Endpoint reverse geocode memvalidasi rentang koordinat lat dan lng.
     */
    public function test_endpoint_reverse_geocode_validasi_koordinat(): void
    {
        $response = $this->actingAs($this->customer)->getJson(route('customer.pesanan.reverse-geocode', [
            'lat' => 100, // Latitude > 90 invalid
            'lng' => 106.8456,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lat']);
    }
}
