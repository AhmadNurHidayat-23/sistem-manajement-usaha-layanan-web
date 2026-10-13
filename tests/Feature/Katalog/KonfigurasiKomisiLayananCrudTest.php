<?php

namespace Tests\Feature\Katalog;

use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Models\Employee;
use App\Models\EmployeeCommission;
use App\Models\Service;
use Tests\Feature\Katalog\Support\KonfigurasiKomisiTestCase;

class KonfigurasiKomisiLayananCrudTest extends KonfigurasiKomisiTestCase
{
    public function test_owner_dapat_membuat_layanan_dengan_komisi_persentase(): void
    {
        $response = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Mobil Reguler',
            'price' => 50000,
            'estimated_duration' => 30,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Cuci Mobil Reguler',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
        ]);

        $service = Service::where('name', 'Cuci Mobil Reguler')->first();
        $this->assertTrue($service->hasCommissionConfigured());
        $this->assertEquals('10%', $service->formatted_commission);
    }

    public function test_owner_dapat_membuat_layanan_dengan_komisi_nominal_tetap(): void
    {
        $response = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Ganti Oli Mesin',
            'price' => 150000,
            'estimated_duration' => 45,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 25000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Ganti Oli Mesin',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 25000,
        ]);

        $service = Service::where('name', 'Ganti Oli Mesin')->first();
        $this->assertTrue($service->hasCommissionConfigured());
        $this->assertEquals('Rp 25.000', $service->formatted_commission);
    }

    public function test_dua_layanan_dapat_memiliki_skema_komisi_berbeda(): void
    {
        $serviceA = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Cuci',
            'price' => 40000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $serviceB = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Poles Body',
            'price' => 300000,
            'service_type' => 'package',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 50000,
            'is_active' => true,
        ]);

        $this->assertEquals(CommissionType::Percentage, $serviceA->fresh()->commission_type);
        $this->assertEquals(10.00, (float) $serviceA->fresh()->commission_value);

        $this->assertEquals(CommissionType::Fixed, $serviceB->fresh()->commission_type);
        $this->assertEquals(50000.00, (float) $serviceB->fresh()->commission_value);
    }

    public function test_owner_dapat_memperbarui_skema_komisi_layanan(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Detailing Interior',
            'price' => 200000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->put(route('catalog.services.update', $service), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Detailing Interior Update',
            'price' => 220000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 40000,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $service->refresh();
        $this->assertEquals(CommissionType::Fixed, $service->commission_type);
        $this->assertEquals(40000.00, (float) $service->commission_value);
    }

    public function test_layanan_tanpa_komisi_tersimpan_sebagai_nullable_belum_diatur(): void
    {
        $response = $this->actingAs($this->owner)->post(route('catalog.services.store'), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Belum Diatur',
            'price' => 35000,
            'service_type' => 'single',
            'commission_type' => null,
            'commission_value' => null,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('catalog.services.index'));
        $service = Service::where('name', 'Layanan Belum Diatur')->first();
        $this->assertNull($service->commission_type);
        $this->assertNull($service->commission_value);
        $this->assertFalse($service->hasCommissionConfigured());
        $this->assertEquals('Belum diatur', $service->formatted_commission);
    }

    public function test_pembaruan_komisi_layanan_tidak_mengubah_riwayat_komisi_historis(): void
    {
        $employee = Employee::create([
            'user_id' => $this->staff->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Ahmad Teknisi',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Service AC Mobil',
            'price' => 100000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $historicCommission = EmployeeCommission::create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'commission_amount' => 10000,
            'status' => CommissionStatus::Paid->value,
        ]);

        // Perbarui tarif komisi master service
        $this->actingAs($this->owner)->put(route('catalog.services.update', $service), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Service AC Mobil',
            'price' => 120000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Fixed->value,
            'commission_value' => 25000,
            'is_active' => true,
        ]);

        // Riwayat komisi lama harus tetap utuh
        $historicCommission->refresh();
        $this->assertEquals(CommissionType::Percentage, $historicCommission->commission_type);
        $this->assertEquals(10.00, (float) $historicCommission->commission_value);
        $this->assertEquals(10000.00, (float) $historicCommission->commission_amount);
        $this->assertEquals(CommissionStatus::Paid, $historicCommission->status);
    }

    public function test_pengosongan_konfigurasi_komisi_dan_keutuhan_riwayat_historis(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Service Konfigurasi Awal',
            'price' => 100000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'user_id' => $this->staff->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Teknisi',
            'phone' => '081298765432',
            'status' => 'active',
        ]);

        $historicCommission = EmployeeCommission::create([
            'employee_id' => $employee->id,
            'service_id' => $service->id,
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'commission_amount' => 10000,
            'status' => CommissionStatus::Paid->value,
        ]);

        $this->actingAs($this->owner)->get(route('catalog.services.edit', $service))
            ->assertStatus(200)
            ->assertSee('Edit Layanan: Service Konfigurasi Awal')
            ->assertSee('-- Belum Diatur --');

        $this->actingAs($this->owner)->put(route('catalog.services.update', $service), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Service Konfigurasi Awal',
            'price' => 100000,
            'service_type' => 'single',
            'commission_type' => '',
            'commission_value' => '',
            'is_active' => true,
        ])->assertRedirect(route('catalog.services.index'))
            ->assertSessionHasNoErrors();

        $service->refresh();
        $this->assertNull($service->commission_type);
        $this->assertNull($service->commission_value);
        $this->assertFalse($service->hasCommissionConfigured());
        $this->assertEquals('Belum diatur', $service->formatted_commission);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'commission_type' => null,
            'commission_value' => null,
        ]);

        $this->actingAs($this->owner)->get(route('catalog.services.show', $service))
            ->assertOk()
            ->assertSee('Belum diatur');
        $this->actingAs($this->owner)->get(route('catalog.services.index'))
            ->assertOk()
            ->assertSee('Komisi: Belum diatur');

        $historicCommission->refresh();
        $this->assertEquals(CommissionType::Percentage, $historicCommission->commission_type);
        $this->assertEquals(10.00, (float) $historicCommission->commission_value);
        $this->assertEquals(10000.00, (float) $historicCommission->commission_amount);
        $this->assertEquals(CommissionStatus::Paid, $historicCommission->status);
    }

    public function test_update_layanan_tanpa_field_komisi_tidak_menghapus_konfigurasi_lama(): void
    {
        $service = Service::create([
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Komisi Parsial',
            'price' => 80000,
            'service_type' => 'single',
            'commission_type' => CommissionType::Percentage->value,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->owner)->put(route('catalog.services.update', $service), [
            'service_category_id' => $this->category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Layanan Komisi Parsial Updated',
            'price' => 95000,
            'service_type' => 'single',
            'is_active' => true,
        ])->assertRedirect(route('catalog.services.index'));

        $service->refresh();
        $this->assertEquals('Layanan Komisi Parsial Updated', $service->name);
        $this->assertEquals(95000.00, (float) $service->price);
        $this->assertEquals(CommissionType::Percentage, $service->commission_type);
        $this->assertEquals(10.00, (float) $service->commission_value);
    }
}
