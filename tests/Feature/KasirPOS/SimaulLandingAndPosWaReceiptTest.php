<?php

namespace Tests\Feature\KasirPOS;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SimaulLandingAndPosWaReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $owner;

    protected User $manager;

    protected User $cashier;

    protected User $employee;

    protected User $customer;

    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.whatsapp.driver' => 'baileys']);
        $this->seed(PeranSeeder::class);

        $this->branch = Branch::create([
            'name' => 'SIMAUL Pusat',
            'address' => 'Jl. Merdeka No. 10',
            'is_active' => true,
        ]);

        $ownerRole = Role::where('name', UserRole::Owner->value)->first();
        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $employeeRole = Role::where('name', UserRole::Employee->value)->first();
        $customerRole = Role::where('name', UserRole::Customer->value)->first();

        $this->owner = User::factory()->create([
            'role_id' => $ownerRole->id,
            'email' => 'owner@simaul.test',
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'email' => 'manager@simaul.test',
        ]);
        $this->manager->branches()->attach($this->branch);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'email' => 'cashier@simaul.test',
        ]);
        $this->cashier->branches()->attach($this->branch);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee@simaul.test',
        ]);
        $this->employee->branches()->attach($this->branch);

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'email' => 'customer@simaul.test',
            'phone' => '081234567890',
        ]);

        $category = ServiceCategory::create(['name' => 'Perawatan Rambut']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'branch_id' => $this->branch->id,
            'name' => 'Potong Rambut Premium',
            'price' => 50000,
            'service_type' => 'appointment',
            'estimated_duration' => 30,
            'is_active' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Landing Page & Alur Login
    // ─────────────────────────────────────────────────────────────────────────

    public function test_guest_accesses_root_displays_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertDontSee('id="header-dashboard-btn"', false);
        $response->assertDontSee('id="header-logout-btn"', false);
    }

    public function test_authenticated_users_are_redirected_to_their_respective_dashboards_when_visiting_root(): void
    {
        $matrix = [
            [$this->owner, route('owner.dashboard')],
            [$this->manager, route('manager.dashboard')],
            [$this->cashier, route('cashier.dashboard')],
            [$this->employee, route('employee.dashboard')],
            [$this->customer, route('customer.dashboard')],
        ];

        foreach ($matrix as [$user, $expectedUrl]) {
            $response = $this->actingAs($user)->get('/');
            $response->assertRedirect($expectedUrl);
        }
    }

    public function test_refresh_and_tab_access_while_logged_in_redirects_to_dashboard(): void
    {
        $this->actingAs($this->cashier);

        // Simulasi akses tab baru atau refresh URL root
        $response1 = $this->get('/');
        $response1->assertRedirect(route('cashier.dashboard'));

        $response2 = $this->get('/');
        $response2->assertRedirect(route('cashier.dashboard'));
    }

    public function test_authenticated_user_accessing_login_page_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->manager)->get('/login');

        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_logout_ends_session_and_redirects_to_landing_page(): void
    {
        $this->actingAs($this->customer);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();

        $landing = $this->get('/');
        $landing->assertStatus(200);
        $landing->assertSee('Login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. WhatsApp POS Receipt
    // ─────────────────────────────────────────────────────────────────────────

    protected function createPaidTransaction(bool $withCustomer = false): Transaction
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-'.uniqid(),
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Paid,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $tx->id,
            'service_id' => $this->service->id,
            'price' => 50000,
            'qty' => 1,
            'subtotal' => 50000,
        ]);

        Payment::create([
            'transaction_id' => $tx->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'status' => StatusPembayaran::Completed,
            'paid_at' => now(),
        ]);

        return $tx;
    }

    public function test_send_receipt_for_general_customer_with_custom_phone_via_baileys(): void
    {
        $tx = $this->createPaidTransaction();

        Http::fake([
            'http://127.0.0.1:3001/send-message' => Http::response([
                'success' => true,
                'messageId' => 'BAI-MSG-TEST-123',
                'phone' => '6285712345678',
            ], 200),
        ]);

        $usersCountBefore = User::count();

        $response = $this->actingAs($this->cashier)
            ->from(route('pos.transactions.receipt', $tx))
            ->post(route('pos.transactions.send-receipt', $tx), [
                'phone' => '085712345678',
            ]);

        $response->assertRedirect(route('pos.transactions.receipt', $tx));
        $response->assertSessionHas('success');

        // Pastikan akun baru tidak dibuat
        $this->assertEquals($usersCountBefore, User::count());

        // Verifikasi log notifikasi tersimpan dengan nomor ternormalisasi
        $this->assertDatabaseHas('notification_logs', [
            'type' => 'transaction_receipt',
            'recipient' => '6285712345678',
            'status' => 'sent',
            'provider_reference' => 'BAI-MSG-TEST-123',
            'user_id' => null,
        ]);

        // Verifikasi panggilan Baileys
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3001/send-message'
                && $request['phone'] === '6285712345678'
                && str_contains($request['message'], 'STRUK TRANSAKSI');
        });
    }

    public function test_send_receipt_fails_when_general_customer_has_no_phone_provided(): void
    {
        $tx = $this->createPaidTransaction();

        $response = $this->actingAs($this->cashier)
            ->from(route('pos.transactions.receipt', $tx))
            ->post(route('pos.transactions.send-receipt', $tx));

        $response->assertRedirect(route('pos.transactions.receipt', $tx));
        $response->assertSessionHas('error', 'Nomor telepon pelanggan belum tercatat atau tidak valid.');
    }

    public function test_send_receipt_fails_with_invalid_phone_format(): void
    {
        $tx = $this->createPaidTransaction();

        $response = $this->actingAs($this->cashier)
            ->from(route('pos.transactions.receipt', $tx))
            ->post(route('pos.transactions.send-receipt', $tx), [
                'phone' => '012345',
            ]);

        $response->assertRedirect(route('pos.transactions.receipt', $tx));
        $response->assertSessionHas('error');
    }

    public function test_send_receipt_handles_baileys_gateway_offline_gracefully(): void
    {
        $tx = $this->createPaidTransaction();

        Http::fake([
            'http://127.0.0.1:3001/send-message' => function () {
                throw new ConnectionException('Connection refused at port 3001');
            },
        ]);

        $response = $this->actingAs($this->cashier)
            ->from(route('pos.transactions.receipt', $tx))
            ->post(route('pos.transactions.send-receipt', $tx), [
                'phone' => '081234567890',
            ]);

        $response->assertRedirect(route('pos.transactions.receipt', $tx));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Gagal mengirim struk via WhatsApp', session('error'));
    }

    public function test_unauthorized_cashier_cannot_send_receipt(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Lain',
            'address' => 'Jl. Lain No. 99',
            'is_active' => true,
        ]);

        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();
        $otherCashier = User::factory()->create([
            'role_id' => $cashierRole->id,
        ]);
        $otherCashier->branches()->attach($otherBranch);

        $tx = $this->createPaidTransaction();

        $response = $this->actingAs($otherCashier)
            ->post(route('pos.transactions.send-receipt', $tx), [
                'phone' => '081234567890',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_send_receipt_for_unpaid_transaction(): void
    {
        $tx = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-UNPAID-'.uniqid(),
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->cashier)
            ->from(route('pos.transactions.receipt', $tx))
            ->post(route('pos.transactions.send-receipt', $tx), [
                'phone' => '081234567890',
            ]);

        $response->assertRedirect(route('pos.transactions.receipt', $tx));
        $response->assertSessionHas('error', 'Struk hanya dapat dikirim untuk transaksi yang sudah lunas.');
    }
}
