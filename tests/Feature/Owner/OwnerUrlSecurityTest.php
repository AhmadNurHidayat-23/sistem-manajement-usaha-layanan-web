<?php

namespace Tests\Feature\Owner;

use App\Enums\DiscountType;
use App\Enums\PromotionStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerUrlSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Branch $branch;

    protected Service $service;

    protected Employee $employee;

    protected Promotion $promotion;

    protected User $managerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->firstOrFail();
        $managerRole = Role::where('name', UserRole::Manager->value)->firstOrFail();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Barbershop & Studio (Cabang Pusat)',
            'address' => 'Jl. MH Thamrin No. 10',
            'service_mode' => 'appointment',
            'max_slot_capacity' => 4,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Grooming',
            'branch_id' => $this->branch->id,
        ]);

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Executive Cut',
            'price' => 75000,
            'service_type' => 'single',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bambang Stylist',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $this->promotion = Promotion::create([
            'branch_id' => $this->branch->id,
            'code' => 'PROMO10',
            'name' => 'Promo Diskon 10rb',
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 10000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
            'status' => PromotionStatus::Active,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Test',
            'role_id' => $ownerRole->id,
        ]);

        $this->managerUser = User::factory()->create([
            'name' => 'Manager Test',
            'role_id' => $managerRole->id,
        ]);
        $this->managerUser->branches()->sync([$this->branch->id]);
    }

    public function test_branch_urls_use_opaque_uuid_identifiers(): void
    {
        $showUrl = route('branches.show', $this->branch);
        $editUrl = route('branches.edit', $this->branch);

        $this->assertStringContainsString($this->branch->uuid, $showUrl);
        $this->assertNotEquals(url('/branches/'.$this->branch->id), $showUrl);
        $this->assertStringContainsString($this->branch->uuid, $editUrl);

        $this->actingAs($this->owner)->get($showUrl)->assertStatus(200);
        $this->actingAs($this->owner)->get($editUrl)->assertStatus(200);

        // Fallback backward compatibility for numeric ID
        $this->actingAs($this->owner)->get('/branches/'.$this->branch->id)->assertStatus(200);
    }

    public function test_service_urls_use_opaque_uuid_identifiers(): void
    {
        $showUrl = route('catalog.services.show', $this->service);
        $editUrl = route('catalog.services.edit', $this->service);

        $this->assertStringContainsString($this->service->uuid, $showUrl);
        $this->assertNotEquals(url('/catalog/services/'.$this->service->id), $showUrl);

        $this->actingAs($this->owner)->get($showUrl)->assertStatus(200);
        $this->actingAs($this->owner)->get($editUrl)->assertStatus(200);

        // Fallback backward compatibility for numeric ID
        $this->actingAs($this->owner)->get('/catalog/services/'.$this->service->id)->assertStatus(200);
    }

    public function test_promotion_urls_use_opaque_uuid_identifiers(): void
    {
        $editUrl = route('promotions.edit', $this->promotion);

        $this->assertStringContainsString($this->promotion->uuid, $editUrl);
        $this->assertNotEquals(url('/promotions/'.$this->promotion->id.'/edit'), $editUrl);

        $this->actingAs($this->owner)->get($editUrl)->assertStatus(200);

        // Fallback backward compatibility
        $this->actingAs($this->owner)->get('/promotions/'.$this->promotion->id.'/edit')->assertStatus(200);
    }

    public function test_employee_urls_use_opaque_uuid_identifiers(): void
    {
        $showUrl = route('employees.show', $this->employee);
        $editUrl = route('employees.edit', $this->employee);

        $this->assertStringContainsString($this->employee->uuid, $showUrl);
        $this->assertNotEquals(url('/employees/'.$this->employee->id), $showUrl);

        $this->actingAs($this->owner)->get($showUrl)->assertStatus(200);
        $this->actingAs($this->owner)->get($editUrl)->assertStatus(200);

        // Fallback backward compatibility
        $this->actingAs($this->owner)->get('/employees/'.$this->employee->id)->assertStatus(200);
    }

    public function test_internal_user_urls_use_opaque_uuid_identifiers(): void
    {
        $editUrl = route('owner.internal-users.edit', $this->managerUser);

        $this->assertStringContainsString($this->managerUser->uuid, $editUrl);
        $this->assertNotEquals(url('/owner/internal-users/'.$this->managerUser->id.'/edit'), $editUrl);

        $this->actingAs($this->owner)->get($editUrl)->assertStatus(200);

        // Fallback backward compatibility
        $this->actingAs($this->owner)->get('/owner/internal-users/'.$this->managerUser->id.'/edit')->assertStatus(200);
    }
}
