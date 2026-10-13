<?php

namespace Tests\Feature\Booking;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\MobileAccessToken;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceModeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $appointmentBranch;

    protected Branch $dropOffBranch;

    protected Branch $bothBranch;

    protected Service $appointmentService;

    protected Service $dropOffService;

    protected Service $bothService;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->firstOrFail();
        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);

        $this->appointmentBranch = Branch::create([
            'name' => 'Cabang Janji Temu',
            'address' => 'Jl. Appointment',
            'service_mode' => 'appointment',
            'max_slot_capacity' => 4,
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'is_active' => true,
        ]);

        $this->dropOffBranch = Branch::create([
            'name' => 'Cabang Drop-Off',
            'address' => 'Jl. Drop Off',
            'service_mode' => 'drop_off',
            'max_slot_capacity' => 4,
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'is_active' => true,
        ]);

        $this->bothBranch = Branch::create([
            'name' => 'Cabang Serbaguna',
            'address' => 'Jl. Both',
            'service_mode' => 'both',
            'max_slot_capacity' => 4,
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Perawatan', 'description' => 'Perawatan']);

        $this->appointmentService = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->appointmentBranch->id,
            'name' => 'Haircut Studio',
            'price' => 50000,
            'estimated_duration' => 45,
            'service_type' => 'appointment',
            'is_active' => true,
        ]);

        $this->dropOffService = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->dropOffBranch->id,
            'name' => 'Laundry Reguler',
            'price' => 8000,
            'estimated_duration' => 60,
            'service_type' => 'drop_off',
            'is_active' => true,
        ]);

        $this->bothService = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->bothBranch->id,
            'name' => 'Cuci & Detailing Sepatu',
            'price' => 35000,
            'estimated_duration' => 60,
            'service_type' => 'both',
            'is_active' => true,
        ]);
    }

    public function test_booking_appointment_succeeds_on_appointment_and_both_branches(): void
    {
        // 1. Appointment branch
        $resAppt = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->appointmentBranch->id,
            'service_id' => $this->appointmentService->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
            'notes' => 'Janji temu appointment',
        ]);
        $resAppt->assertSessionHas('success');

        // 2. Both branch
        $resBoth = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->bothBranch->id,
            'service_id' => $this->bothService->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '11:00',
            'notes' => 'Janji temu cabang both',
        ]);
        $resBoth->assertSessionHas('success');
    }

    public function test_booking_appointment_fails_on_drop_off_only_branch(): void
    {
        $response = $this->actingAs($this->customer)->post(route('bookings.store'), [
            'branch_id' => $this->dropOffBranch->id,
            'service_id' => $this->dropOffService->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['branch_id']);
    }

    public function test_order_drop_off_succeeds_on_drop_off_and_both_branches(): void
    {
        // 1. Drop-off branch
        $resDrop = $this->actingAs($this->customer)->post(route('orders.store'), [
            'branch_id' => $this->dropOffBranch->id,
            'service_id' => $this->dropOffService->id,
            'estimated_quantity' => 3,
        ]);
        $resDrop->assertSessionHas('success');

        // 2. Both branch
        $resBoth = $this->actingAs($this->customer)->post(route('orders.store'), [
            'branch_id' => $this->bothBranch->id,
            'service_id' => $this->bothService->id,
            'estimated_quantity' => 2,
        ]);
        $resBoth->assertSessionHas('success');
    }

    public function test_order_drop_off_fails_on_appointment_only_branch(): void
    {
        $response = $this->actingAs($this->customer)->post(route('orders.store'), [
            'branch_id' => $this->appointmentBranch->id,
            'service_id' => $this->appointmentService->id,
            'estimated_quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['branch_id']);
    }

    public function test_mobile_api_validates_service_mode_for_bookings_and_orders(): void
    {
        $tokenData = MobileAccessToken::generateForUser($this->customer);
        $token = $tokenData['token'];
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];

        // 1. Mobile booking on drop-off branch should be rejected with 422
        $resApptFail = $this->postJson('/api/mobile/customer/bookings', [
            'branch_id' => $this->dropOffBranch->id,
            'service_id' => $this->dropOffService->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
        ], $headers);

        $resApptFail->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cabang yang dipilih tidak melayani booking janji temu (appointment).',
            ]);

        // 2. Mobile order on appointment-only branch should be rejected with 422
        $resOrderFail = $this->postJson('/api/mobile/customer/orders', [
            'branch_id' => $this->appointmentBranch->id,
            'service_id' => $this->appointmentService->id,
            'estimated_quantity' => 2,
        ], $headers);

        $resOrderFail->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cabang yang dipilih tidak melayani pesanan drop-off.',
            ]);
    }
}
