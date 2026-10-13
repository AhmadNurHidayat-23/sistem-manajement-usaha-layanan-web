<?php

namespace Tests\Feature\Booking;

use App\Enums\StatusPesanan;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaOrderDropoffTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $customer;

    protected Branch $branch1;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '0811111111',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'service_mode' => 'both',
            'max_slot_capacity' => 2,
            'is_active' => true,
        ]);

        $category = ServiceCategory::create([
            'name' => 'Grooming',
            'description' => 'Layanan grooming',
        ]);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Cuci Premium',
            'description' => 'Cuci dan detailing',
            'price' => 75000,
            'estimated_duration' => 90,
            'service_type' => 'both',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_customer_can_create_drop_off_order_and_estimated_finish_is_calculated(): void
    {
        $response = $this->actingAs($this->customer)->post(route('orders.store'), [
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'estimated_quantity' => 3.5,
            'pickup_option' => 1,
            'notes' => 'Tolong hati-hati',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals($this->branch1->id, $order->branch_id);
        $this->assertEquals('3.50', $order->estimated_quantity);
        $this->assertTrue((bool) $order->pickup_option);
        $this->assertEquals(StatusPesanan::Pending, $order->status);
        $this->assertNotNull($order->estimated_finish_at);
    }

    public function test_authorized_user_can_update_order_status_and_cancel_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch1->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Pending,
        ]);

        $response = $this->actingAs($this->manager)->patch(route('orders.update-status', $order), [
            'status' => StatusPesanan::Received->value,
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(StatusPesanan::Received, $order->fresh()->status);

        $cancelResponse = $this->actingAs($this->customer)->patch(route('orders.cancel', $order));
        $cancelResponse->assertRedirect(route('orders.index'));
        $this->assertEquals(StatusPesanan::Cancelled, $order->fresh()->status);
    }
}
