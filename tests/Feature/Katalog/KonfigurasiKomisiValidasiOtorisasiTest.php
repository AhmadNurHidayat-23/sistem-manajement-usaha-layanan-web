<?php

namespace Tests\Feature\Katalog;

use App\Enums\CommissionType;
use App\Models\Service;
use Tests\Feature\Katalog\Support\KonfigurasiKomisiTestCase;

class KonfigurasiKomisiValidasiOtorisasiTest extends KonfigurasiKomisiTestCase
{
    public function test_persentase_komisi_di_luar_rentang_0_sampai_100_ditolak(): void
    {
        // Nilai persentase lebih dari 100
        $responseMax = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Invalid Max',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 105,
        ]);
        $responseMax->assertSessionHasErrors('commission_value');

        // Nilai persentase kurang dari 0
        $responseMin = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Invalid Min',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => -5,
        ]);
        $responseMin->assertSessionHasErrors('commission_value');
    }

    public function test_nominal_komisi_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Invalid Nominal',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => -1000,
        ]);
        $response->assertSessionHasErrors('commission_value');
    }

    public function test_tipe_komisi_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Invalid Tipe',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => 'custom_tier',
            'commission_value' => 10,
        ]);
        $response->assertSessionHasErrors('commission_type');
    }

    public function test_tipe_dan_nilai_komisi_yang_inkonsisten_ditolak(): void
    {
        // Tipe diisi tapi nilai kosong
        $resTypeOnly = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Tipe Saja',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => null,
        ]);
        $resTypeOnly->assertSessionHasErrors('commission_value');

        // Nilai diisi tapi tipe kosong
        $resValOnly = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Nilai Saja',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => null,
            'commission_value' => 20,
        ]);
        $resValOnly->assertSessionHasErrors('commission_type');
    }

    public function test_owner_dapat_mengelola_komisi_layanan_seluruh_cabang(): void
    {
        // Owner buat layanan di Cabang 1
        $resBranch1 = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Owner Cabang 1',
            'price' => 60000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 12,
        ]);
        $resBranch1->assertRedirect();

        // Owner buat layanan di Cabang 2
        $resBranch2 = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Owner Cabang 2',
            'price' => 70000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 15000,
        ]);
        $resBranch2->assertRedirect();

        $this->assertDatabaseHas('services', ['name' => 'Layanan Owner Cabang 1']);
        $this->assertDatabaseHas('services', ['name' => 'Layanan Owner Cabang 2']);
    }

    public function test_manager_dapat_mengelola_komisi_layanan_cabang_kelolaannya(): void
    {
        $response = $this->actingAs($this->managerBranch1)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Manager Tebet',
            'price' => 80000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Layanan Manager Tebet',
            'branch_id' => $this->branch1->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
        ]);
    }

    public function test_manager_ditolak_mengelola_komisi_layanan_cabang_lain(): void
    {
        // Manager Tebet mencoba membuat layanan untuk Cabang Kemang (Cabang 2)
        $responseStore = $this->actingAs($this->managerBranch1)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Ilegal Kemang',
            'price' => 80000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
        ]);
        $responseStore->assertForbidden();

        // Manager Tebet mencoba mengedit layanan milik Cabang Kemang
        $serviceBranch2 = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Asli Kemang',
            'price' => 90000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $responseUpdate = $this->actingAs($this->managerBranch1)->put(route('catalog.services.update', $serviceBranch2), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Asli Kemang Tampered',
            'price' => 95000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 20000,
        ]);
        $responseUpdate->assertForbidden();
    }

    public function test_pengguna_tanpa_kewenangan_ditolak_mengelola_komisi_layanan(): void
    {
        $response = $this->actingAs($this->staff)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Staff Unauthorized',
            'price' => 50000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
        ]);

        $response->assertForbidden();
    }

    public function test_owner_dapat_melihat_dan_mengubah_layanan_global(): void
    {
        $globalService = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => null,
            'name' => 'Layanan Global Perusahaan',
            'price' => 100000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->owner)->get(route('catalog.services.index'))->assertOk()->assertSee('Layanan Global Perusahaan');
        $this->actingAs($this->owner)->get(route('catalog.services.show', $globalService))->assertOk()->assertSee('Layanan Global Perusahaan');
        $this->actingAs($this->owner)->get(route('catalog.services.edit', $globalService))->assertOk();

        $this->actingAs($this->owner)->put(route('catalog.services.update', $globalService), [
            'service_category_id' => $this->category->id,
            'branch_id' => null,
            'name' => 'Layanan Global Diubah Owner',
            'price' => 110000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 15,
            'is_active' => true,
        ])->assertRedirect(route('catalog.services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $globalService->id,
            'name' => 'Layanan Global Diubah Owner',
            'commission_value' => 15,
        ]);
    }

    public function test_perilaku_manager_terhadap_layanan_global_dan_proteksi_manipulasi_cabang(): void
    {
        $globalService = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => null,
            'name' => 'Layanan Global Untuk Manager',
            'price' => 100000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        // Manager dapat melihat layanan global pada katalog
        $this->actingAs($this->managerBranch1)->get(route('catalog.services.index'))->assertOk()->assertSee('Layanan Global Untuk Manager');
        $this->actingAs($this->managerBranch1)->get(route('catalog.services.show', $globalService))->assertOk();
        $this->actingAs($this->managerBranch1)->get(route('catalog.services.edit', $globalService))->assertOk();

        // Manager dapat mengubah layanan global jika branch_id tetap global (perilaku aktual kode saat ini)
        $this->actingAs($this->managerBranch1)->put(route('catalog.services.update', $globalService), [
            'service_category_id' => $this->category->id,
            'branch_id' => null,
            'name' => 'Layanan Global Diubah Manager',
            'price' => 105000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 12,
            'is_active' => true,
        ])->assertRedirect(route('catalog.services.index'));

        // Manager ditolak memanipulasi branch_id menjadi cabang lain di luar kewenangannya
        $this->actingAs($this->managerBranch1)->put(route('catalog.services.update', $globalService), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Layanan Global Dibajak ke Kemang',
            'price' => 105000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 12,
            'is_active' => true,
        ])->assertForbidden();
    }
}
