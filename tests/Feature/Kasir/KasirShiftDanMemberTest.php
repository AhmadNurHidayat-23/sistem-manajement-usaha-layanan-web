<?php

namespace Tests\Feature\Kasir;

use App\Enums\CashierShiftStatus;
use App\Models\CashierShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirShiftDanMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->artisan('simaul:setup-demo-kasir');
    }

    public function test_kasir_dapat_menutup_dan_membuka_kembali_shift_kasir(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $activeShift = CashierShift::where('user_id', $kasir->id)
            ->where('status', CashierShiftStatus::Open)
            ->first();

        $this->assertNotNull($activeShift);

        // Tutup shift
        $responseClose = $this->actingAs($kasir)->post(route('pos.shift.close'), [
            'branch_id' => 1,
            'closing_cash' => 280000,
            'notes' => 'Kas dihitung sesuai ekspektasi.',
        ]);

        $responseClose->assertRedirect();
        $responseClose->assertSessionHas('success');
        $this->assertEquals(CashierShiftStatus::Closed, $activeShift->fresh()->status);

        // Buka shift baru
        $responseOpen = $this->actingAs($kasir)->post(route('pos.shift.open'), [
            'branch_id' => 1,
            'opening_cash' => 300000,
        ]);

        $responseOpen->assertRedirect();
        $responseOpen->assertSessionHas('success');
        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $kasir->id,
            'branch_id' => 1,
            'status' => CashierShiftStatus::Open->value,
            'opening_cash' => 300000,
        ]);

        // Pencegahan buka shift ganda
        $responseDoubleOpen = $this->actingAs($kasir)->post(route('pos.shift.open'), [
            'branch_id' => 1,
            'opening_cash' => 200000,
        ]);
        $responseDoubleOpen->assertSessionHasErrors(['shift']);
    }

    public function test_kasir_dapat_mendaftarkan_pelanggan_baru_sebagai_member(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();

        $response = $this->actingAs($kasir)->post(route('members.store'), [
            'name' => 'Pelanggan Baru Walkin',
            'phone' => '089912345678',
            'email' => 'walkin.baru@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('phone', '089912345678')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->membership);
        $this->assertEquals(10, $user->membership->loyaltyPoint?->total_points);
    }

    public function test_kasir_dapat_mendaftarkan_pelanggan_existing_dan_mencegah_duplikasi_membership(): void
    {
        $kasir = User::where('email', 'kasir@simaul.com')->first();
        $nonMember = User::where('email', 'pelanggan.nonmember@simaul.com')->first();
        $alreadyMember = User::where('email', 'pelanggan.member@simaul.com')->first();

        $this->assertNull($nonMember->membership);
        $this->assertNotNull($alreadyMember->membership);

        // Daftarkan pelanggan existing
        $respExisting = $this->actingAs($kasir)->post(route('members.store'), [
            'user_id' => $nonMember->id,
        ]);
        $respExisting->assertRedirect();
        $this->assertNotNull($nonMember->fresh()->membership);

        // Coba daftarkan pelanggan yang sudah member (harus ditolak error validasi)
        $respDuplicate = $this->actingAs($kasir)->post(route('members.store'), [
            'user_id' => $alreadyMember->id,
        ]);
        $respDuplicate->assertSessionHasErrors(['user_id']);
    }
}
