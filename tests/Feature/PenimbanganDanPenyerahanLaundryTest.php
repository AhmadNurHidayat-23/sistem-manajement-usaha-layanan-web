<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\BookingAntrian\StatusPekerjaanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PenimbanganDanPenyerahanLaundryTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $employee;

    protected Employee $employeeRecord;

    protected User $customer;

    protected Branch $branch;

    protected Service $laundryService;

    protected function setUp(): void
    {
        parent::setUp();

        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Laundry & Shoe Care',
            'address' => 'Jl. Pengujian Laundry No. 1',
            'phone' => '081234567890',
            'opening_time' => '08:00:00',
            'closing_time' => '21:00:00',
            'service_mode' => 'drop_off',
            'max_slot_capacity' => 10,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashier->branches()->attach($this->branch->id);

        $this->employee = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->employee->branches()->attach($this->branch->id);

        $this->employeeRecord = Employee::create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'name' => 'Agus Santoso',
            'phone' => '081234567888',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create(['role_id' => $customerRole->id, 'phone' => '081234567899']);

        $category = ServiceCategory::firstOrCreate(
            ['name' => 'Laundry Kiloan'],
            ['description' => 'Kategori laundry kiloan']
        );

        $this->laundryService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Komplit Reguler',
            'price' => 9000,
            'unit' => 'kg',
            'service_type' => 'drop_off',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);
    }

    public function test_karyawan_cannot_start_weight_based_order_before_weighed(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'employee_id' => $this->employeeRecord->id,
            'status' => StatusPesanan::Received,
            'estimated_quantity' => 3,
        ]);

        $statusService = app(StatusPekerjaanService::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('harus ditimbang');

        $statusService->updateOrderStatus($this->employee, $order, StatusPesanan::Processing->value);
    }

    public function test_kasir_can_record_weight_and_calculate_bill(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Received,
            'estimated_quantity' => 3,
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.orders.weigh', $order), [
                'actual_weight' => 4.5,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.actual_weight', 4.5)
            ->assertJsonPath('order.total_amount', 40500);

        $order->refresh();
        $this->assertEquals(4.5, (float) $order->actual_weight);
        $this->assertEquals(40500, (float) $order->total_amount);
        $this->assertNotNull($order->weighed_at);
        $this->assertEquals($this->cashier->id, $order->weighed_by);
    }

    public function test_karyawan_can_start_order_after_weighed(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'employee_id' => $this->employeeRecord->id,
            'status' => StatusPesanan::Received,
            'actual_weight' => 4.5,
            'total_amount' => 40500,
            'weighed_at' => now(),
            'weighed_by' => $this->cashier->id,
        ]);

        $statusService = app(StatusPekerjaanService::class);
        $statusService->updateOrderStatus($this->employee, $order, StatusPesanan::Processing->value);

        $order->refresh();
        $this->assertEquals(StatusPesanan::Processing, $order->status);
    }

    public function test_customer_can_select_store_pickup_delivery_method(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Ready,
            'actual_weight' => 4.5,
            'total_amount' => 40500,
            'weighed_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->patchJson(route('customer.pesanan.delivery-method', $order), [
                'delivery_method' => 'store_pickup',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('delivery_method', 'store_pickup')
            ->assertJsonPath('handover_status', 'waiting_pickup');

        $order->refresh();
        $this->assertEquals('store_pickup', $order->delivery_method);
        $this->assertEquals('waiting_pickup', $order->handover_status);
        $this->assertNotNull($order->delivery_selected_at);
    }

    public function test_customer_can_select_address_delivery_method(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Ready,
            'actual_weight' => 4.5,
            'total_amount' => 40500,
            'weighed_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->patchJson(route('customer.pesanan.delivery-method', $order), [
                'delivery_method' => 'address_delivery',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('delivery_method', 'address_delivery')
            ->assertJsonPath('handover_status', 'waiting_payment');

        $order->refresh();
        $this->assertEquals('address_delivery', $order->delivery_method);
        $this->assertEquals('waiting_payment', $order->handover_status);
    }

    public function test_customer_can_initiate_online_payment_with_calculated_bill(): void
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Ready,
            'actual_weight' => 4.5,
            'total_amount' => 40500,
            'weighed_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.pesanan.pay-online', $order));

        $order->refresh();
        $this->assertNotNull($order->transaction);
        $this->assertEquals(40500, (float) $order->transaction->total);

        $response->assertRedirect(route('payment.gateway.show', $order->transaction->uuid));
    }

    public function test_cashier_dashboard_displays_correct_statuses_and_actions(): void
    {
        // 1. Order received tapi belum ditimbang
        $unweighedOrder = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Received,
            'estimated_quantity' => 2,
        ]);

        // 2. Order ready siap diambil/diantar
        $readyOrder = Order::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->customer->id,
            'service_id' => $this->laundryService->id,
            'status' => StatusPesanan::Ready,
            'actual_weight' => 3.0,
            'total_amount' => 27000,
            'weighed_at' => now(),
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('cashier.dashboard', ['branch_id' => $this->branch->id]));

        $response->assertOk();
        $response->assertSee('Pesanan yang Perlu Ditangani');
        $response->assertSee('Belum Ditimbang');
        $response->assertSee('Input Berat');
        $response->assertSee('Siap Diambil / Diantar');
        $response->assertSee('Bayar');
    }
}
