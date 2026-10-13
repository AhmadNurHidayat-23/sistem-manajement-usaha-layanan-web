<?php

namespace Tests\Feature\Layanan;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaLayananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);
    }

    public function test_owner_can_manage_categories(): void
    {
        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $responseIndex = $this->actingAs($owner)->get(route('catalog.categories.index'));
        $responseIndex->assertStatus(200);

        $responseStore = $this->actingAs($owner)->post(route('catalog.categories.store'), [
            'name' => 'Perawatan Sepatu',
            'description' => 'Layanan perawatan dan cuci sepatu',
            'is_active' => 1,
        ]);
        $responseStore->assertRedirect(route('catalog.categories.index'));

        $this->assertDatabaseHas('service_categories', [
            'name' => 'Perawatan Sepatu',
        ]);

        $category = ServiceCategory::where('name', 'Perawatan Sepatu')->first();

        $responseToggle = $this->actingAs($owner)->patch(route('catalog.categories.toggle-status', $category->id));
        $responseToggle->assertRedirect();
        $category->refresh();
        $this->assertFalse($category->is_active);
    }

    public function test_owner_can_create_and_update_service(): void
    {
        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $category = ServiceCategory::create(['name' => 'General', 'is_active' => true]);
        $branch = Branch::create(['name' => 'Cabang Utama', 'address' => 'Jl. Merdeka', 'is_active' => true]);

        $responseCreate = $this->actingAs($owner)->get(route('catalog.services.create'));
        $responseCreate->assertStatus(200);

        $responseStore = $this->actingAs($owner)->post(route('catalog.services.store'), [
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Service Premium Leather',
            'description' => 'Pembersihan bahan kulit premium',
            'price' => 150000,
            'estimated_duration' => 120,
            'service_type' => 'package',
            'is_active' => 1,
        ]);

        $responseStore->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Service Premium Leather',
            'price' => 150000,
            'service_type' => 'package',
        ]);

        $service = Service::where('name', 'Service Premium Leather')->first();

        $responseUpdate = $this->actingAs($owner)->put(route('catalog.services.update', $service->id), [
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Service Premium Leather Updated',
            'description' => 'Deskripsi baru',
            'price' => 175000,
            'estimated_duration' => 150,
            'service_type' => 'package',
            'is_active' => 1,
        ]);

        $responseUpdate->assertRedirect(route('catalog.services.index'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Service Premium Leather Updated',
            'price' => 175000,
        ]);
    }

    public function test_owner_can_view_service_detail_and_edit_form(): void
    {
        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $category = ServiceCategory::create(['name' => 'Kategori Test', 'is_active' => true]);
        $branch = Branch::create(['name' => 'Cabang Test', 'address' => 'Jl. Test', 'is_active' => true]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $branch->id,
            'name' => 'Layanan Detail Test',
            'price' => 50000,
            'estimated_duration' => 45,
            'service_type' => 'unit',
            'is_active' => true,
        ]);

        $responseShow = $this->actingAs($owner)->get(route('catalog.services.show', $service->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Layanan Detail Test');

        $responseEdit = $this->actingAs($owner)->get(route('catalog.services.edit', $service->id));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Layanan Detail Test');
    }
}
