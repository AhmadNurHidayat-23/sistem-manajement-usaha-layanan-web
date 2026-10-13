<?php

namespace Tests\Feature\Ulasan;

use App\Enums\BookingStatus;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\Ulasan;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UlasanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer1;

    protected User $customer2;

    protected User $owner;

    protected Branch $branch;

    protected Service $service;

    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);
        $this->customer1 = User::factory()->create(['role_id' => $customerRole->id]);
        $this->customer2 = User::factory()->create(['role_id' => $customerRole->id]);

        $this->branch = Branch::create([
            'name' => 'Cabang Ulasan',
            'address' => 'Jl. Ulasan No. 1',
            'is_active' => true,
        ]);

        $category = ServiceCategory::create(['name' => 'Umum']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Layanan Servis',
            'price' => 100000,
            'service_type' => 'appointment',
            'estimated_duration' => 60,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'user_id' => $this->customer1->id,
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00',
            'status' => BookingStatus::Completed,
        ]);

        $this->transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'transaction_code' => 'TRX-REV-001',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);
    }

    public function test_customer_can_create_ulasan_for_paid_transaction(): void
    {
        $response = $this->actingAs($this->customer1)->post(route('ulasan.store'), [
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Pelayanan sangat memuaskan dan cepat!',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('ulasans', [
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'rating' => 5,
            'komentar' => 'Pelayanan sangat memuaskan dan cepat!',
        ]);
    }

    public function test_one_transaction_can_only_have_one_ulasan(): void
    {
        Ulasan::create([
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 4,
            'komentar' => 'Bagus',
        ]);

        $response = $this->actingAs($this->customer1)->post(route('ulasan.store'), [
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Mencoba review kedua',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Ulasan::where('transaction_id', $this->transaction->id)->count());
    }

    public function test_customer_can_edit_own_ulasan(): void
    {
        $ulasan = Ulasan::create([
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 4,
            'komentar' => 'Cukup bagus',
        ]);

        $response = $this->actingAs($this->customer1)->put(route('ulasan.update', $ulasan), [
            'rating' => 5,
            'komentar' => 'Setelah dipakai sangat memuaskan!',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertEquals(5, $ulasan->fresh()->rating);
        $this->assertEquals('Setelah dipakai sangat memuaskan!', $ulasan->fresh()->komentar);
    }

    public function test_customer_cannot_edit_other_customer_ulasan(): void
    {
        $ulasan = Ulasan::create([
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 4,
            'komentar' => 'Milik customer 1',
        ]);

        $response = $this->actingAs($this->customer2)->get(route('ulasan.edit', $ulasan));

        $response->assertStatus(403);
    }

    public function test_owner_can_view_ulasan_list(): void
    {
        Ulasan::create([
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Luar biasa bagus',
        ]);

        $response = $this->actingAs($this->owner)->get(route('ulasan.index'));

        $response->assertStatus(200);
        $response->assertSee('Ulasan &amp; Rating', false);
        $response->assertSee('Luar biasa bagus');
    }

    public function test_owner_can_view_ulasan_for_service(): void
    {
        Ulasan::create([
            'user_id' => $this->customer1->id,
            'transaction_id' => $this->transaction->id,
            'service_id' => $this->service->id,
            'rating' => 5,
            'komentar' => 'Servis mantap',
        ]);

        $response = $this->actingAs($this->owner)->get(route('ulasan.by-service', $this->service->id));

        $response->assertStatus(200);
        $response->assertSee('Servis mantap');
    }
}
