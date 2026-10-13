<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusPesanan;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $owner;

    protected Branch $branch;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $customerRole = Role::where('name', UserRole::Customer->value)->first();
        $ownerRole = Role::where('name', UserRole::Owner->value)->first();

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => 'Pak Owner',
            'email' => 'owner@example.com',
        ]);

        $this->branch = Branch::create([
            'name' => 'Laundry Express Sudirman',
            'address' => 'Jl. Sudirman No. 12',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Kiloan']);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Komplit Reguler',
            'description' => 'Cuci, kering, dan setrika rapi.',
            'price' => 10000,
            'service_type' => 'dropoff',
            'estimated_duration' => 120,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_view_member_page_and_register(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.member'));

        $response->assertStatus(200);
        $response->assertSee('Anda Belum Menjadi Member');

        $regResponse = $this->actingAs($this->customer)->post(route('customer.member.register'));
        $regResponse->assertRedirect(route('customer.member'));

        $this->assertDatabaseHas('memberships', [
            'user_id' => $this->customer->id,
        ]);

        $responseAfter = $this->actingAs($this->customer->fresh())->get(route('customer.member'));
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('Member Card');
        $responseAfter->assertDontSee('Daftar Member Sekarang');
    }

    public function test_customer_can_view_promo_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.promo'));

        $response->assertStatus(200);
        $response->assertSee('Promo & Voucher');
    }

    public function test_customer_can_review_completed_order(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'status' => StatusPesanan::Completed,
        ]);

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->owner->id,
            'order_id' => $order->id,
            'transaction_code' => 'TRX-ORDER-001',
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $formResponse = $this->actingAs($this->customer)->get(route('customer.ulasan.create', ['order_id' => $order->id]));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Beri Ulasan');

        $postResponse = $this->actingAs($this->customer)->post(route('customer.ulasan.store'), [
            'order_id' => $order->id,
            'rating' => 5,
            'komentar' => 'Cucian sangat bersih dan wangi semerbak!',
        ]);

        $postResponse->assertRedirect(route('customer.pesanan.show', $order));

        $this->assertDatabaseHas('ulasans', [
            'user_id' => $this->customer->id,
            'transaction_id' => $transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Cucian sangat bersih dan wangi semerbak!',
        ]);

        $duplicateResponse = $this->actingAs($this->customer)->post(route('customer.ulasan.store'), [
            'order_id' => $order->id,
            'rating' => 4,
            'komentar' => 'Mencoba kirim lagi',
        ]);
        $duplicateResponse->assertSessionHas('error');
        $this->assertEquals(1, Ulasan::where('transaction_id', $transaction->id)->count());

        $indexResponse = $this->actingAs($this->customer)->get(route('customer.ulasan.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Cucian sangat bersih dan wangi semerbak!');

        $ulasan = Ulasan::where('transaction_id', $transaction->id)->first();
        $editResponse = $this->actingAs($this->customer)->get(route('customer.ulasan.edit', $ulasan));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Edit Ulasan');

        $updateResponse = $this->actingAs($this->customer)->put(route('customer.ulasan.update', $ulasan), [
            'rating' => 4,
            'komentar' => 'Komentar yang telah diperbarui.',
        ]);
        $updateResponse->assertRedirect(route('customer.ulasan.index'));
        $this->assertEquals(4, $ulasan->fresh()->rating);
        $this->assertEquals('Komentar yang telah diperbarui.', $ulasan->fresh()->komentar);
    }
}
