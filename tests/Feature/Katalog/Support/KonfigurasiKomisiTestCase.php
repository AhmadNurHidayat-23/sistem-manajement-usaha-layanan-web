<?php

namespace Tests\Feature\Katalog\Support;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Base test case untuk fixture pengujian konfigurasi komisi per layanan.
 */
abstract class KonfigurasiKomisiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $managerBranch1;

    protected User $staff;

    protected Branch $branch1;

    protected Branch $branch2;

    protected ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Tebet',
            'address' => 'Jl. Tebet Raya No. 10',
            'phone' => '081211112222',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Kemang',
            'address' => 'Jl. Kemang Raya No. 20',
            'phone' => '081233334444',
            'is_active' => true,
        ]);

        $this->category = ServiceCategory::create([
            'name' => 'Perawatan Kendaraan',
            'description' => 'Kategori layanan umum',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Utama',
            'email' => 'owner@example.com',
            'role_id' => $ownerRole?->id,
        ]);

        $this->managerBranch1 = User::factory()->create([
            'name' => 'Manager Tebet',
            'email' => 'manager.tebet@example.com',
            'role_id' => $managerRole?->id,
        ]);
        $this->managerBranch1->branches()->attach($this->branch1->id);

        $this->staff = User::factory()->create([
            'name' => 'Staff Biasa',
            'email' => 'staff@example.com',
            'role_id' => $employeeRole?->id,
        ]);
    }
}
