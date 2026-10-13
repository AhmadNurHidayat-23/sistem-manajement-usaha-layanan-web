<?php

namespace Tests\Feature\Kasir;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesTransaksiPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $otherCashier;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->branch1 = Branch::create([
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Jendral Sudirman No. 10',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Cabang Thamrin',
            'address' => 'Jl. MH Thamrin No. 20',
            'phone' => '08987654321',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['role_id' => $ownerRole->id]);

        $this->manager = User::factory()->create(['role_id' => $managerRole->id]);
        $this->manager->branches()->attach($this->branch1->id);

        $this->cashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->cashier->branches()->attach($this->branch1->id);

        $this->otherCashier = User::factory()->create(['role_id' => $cashierRole->id]);
        $this->otherCashier->branches()->attach($this->branch2->id);
    }

    public function test_cashier_cannot_access_unassigned_branch_transactions(): void
    {
        $trxBranch2 = Transaction::create([
            'branch_id' => $this->branch2->id,
            'cashier_id' => $this->otherCashier->id,
            'transaction_code' => 'TRX-B2-001',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->cashier)->get(route('pos.transactions.show', $trxBranch2));
        $response->assertStatus(403);
    }

    public function test_manager_can_view_their_branch_transactions_but_not_unassigned(): void
    {
        $trxBranch1 = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-B1-001',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $trxBranch2 = Transaction::create([
            'branch_id' => $this->branch2->id,
            'cashier_id' => $this->otherCashier->id,
            'transaction_code' => 'TRX-B2-002',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $response1 = $this->actingAs($this->manager)->get(route('pos.transactions.show', $trxBranch1));
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->manager)->get(route('pos.transactions.show', $trxBranch2));
        $response2->assertStatus(403);
    }

    public function test_owner_can_view_all_transactions(): void
    {
        $trx = Transaction::create([
            'branch_id' => $this->branch2->id,
            'cashier_id' => $this->otherCashier->id,
            'transaction_code' => 'TRX-B2-003',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->owner)->get(route('pos.transactions.show', $trx));
        $response->assertStatus(200);

        $responseIndex = $this->actingAs($this->owner)->get(route('pos.transactions.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('TRX-B2-003');
    }

    public function test_receipt_view_renders_correctly(): void
    {
        $trx = Transaction::create([
            'branch_id' => $this->branch1->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-RECEIPT-01',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => StatusPembayaran::Completed,
            'reference_number' => 'Tunai | Bayar: Rp 50.000 | Kembali: Rp 0',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->cashier)->get(route('pos.transactions.receipt', $trx));
        $response->assertStatus(200);
        $response->assertSee('TRX-RECEIPT-01');
        $response->assertSee('Cabang Sudirman');
    }
}
