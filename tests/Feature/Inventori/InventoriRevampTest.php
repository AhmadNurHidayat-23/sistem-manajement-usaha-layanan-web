<?php

namespace Tests\Feature\Inventori;

use App\Enums\InventoryMutationType;
use App\Enums\StatusTransaksi;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\Inventori\InventoriTransaksiService;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoriRevampTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $cashier;

    protected Branch $branch;

    protected InventoryItem $pomade;

    protected InventoryItem $cape;

    protected Service $stylingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PeranSeeder::class);

        $managerRole = Role::where('name', UserRole::Manager->value)->first();
        $cashierRole = Role::where('name', UserRole::Cashier->value)->first();

        $this->branch = Branch::create([
            'name' => 'SIMAUL Pusat - Barbershop',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Budi',
            'role_id' => $managerRole->id,
        ]);
        $this->manager->branches()->attach($this->branch->id);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Rian',
            'role_id' => $cashierRole->id,
        ]);
        $this->cashier->branches()->attach($this->branch->id);

        // Buat inventory item
        $this->pomade = InventoryItem::create([
            'branch_id' => $this->branch->id,
            'code' => 'POM-001',
            'name' => 'Pomade Matte Clay Premium 100g',
            'unit' => 'pot',
            'current_stock' => 20.00,
            'minimum_stock' => 5.00,
            'cost_per_unit' => 50000.00,
        ]);

        $this->cape = InventoryItem::create([
            'branch_id' => $this->branch->id,
            'code' => 'CAP-001',
            'name' => 'Cape Barbershop & Neck Paper',
            'unit' => 'pcs',
            'current_stock' => 50.00,
            'minimum_stock' => 10.00,
            'cost_per_unit' => 5000.00,
        ]);

        // Buat service category
        $category = ServiceCategory::create([
            'branch_id' => $this->branch->id,
            'name' => 'Haircut & Styling',
        ]);

        // Buat service
        $this->stylingService = Service::create([
            'branch_id' => $this->branch->id,
            'service_category_id' => $category->id,
            'name' => 'Potong Rambut & Styling',
            'price' => 75000.00,
            'unit' => 'layanan',
            'service_type' => 'appointment',
            'is_active' => true,
        ]);
    }

    /**
     * Uji 1: Halaman detail bahan menampilkan judul nama bahan, breadcrumb, tombol aksi,
     * dan form manual stok keluar sudah dihilangkan.
     */
    public function test_detail_inventori_page_shows_revamped_layout_and_no_manual_stock_out_form(): void
    {
        $response = $this->actingAs($this->manager)->get(route('inventori.show', $this->pomade));

        $response->assertStatus(200);

        // 1. Breadcrumb dan judul
        $response->assertSee('Detail Inventori');
        $response->assertSee('Pomade Matte Clay Premium 100g');
        $response->assertSee('POM-001');
        $response->assertSee('SIMAUL Pusat - Barbershop');

        // 2. Tombol aksi di header
        $response->assertSee('Daftar Bahan');
        $response->assertSee('Edit Bahan');
        $response->assertSee('Tambah Stok');

        // 3. 4 kartu metrik statistik
        $response->assertSee('Stok Saat Ini');
        $response->assertSee('Batas Stok Minimum');
        $response->assertSee('Harga Modal / Satuan');
        $response->assertSee('Total Nilai Persediaan');

        // 4. Judul tabel riwayat stok
        $response->assertSee('Riwayat Stok Bahan');
        $response->assertDontSee('Riwayat Perubahan Stok Bahan Ini');

        // 5. Form manual stok keluar WAJIB tidak ada di antarmuka
        $response->assertDontSee('Catat Stok Keluar (Pemakaian)');
        $response->assertDontSee('route(\'inventori.stock-out\'');

        // 6. Modal Tambah Stok ada dalam HTML
        $response->assertSee('id="modal-tambah-stok"', false);
        $response->assertSee('Tambah Stok Bahan');
        $response->assertSee('Simpan Stok');
        $response->assertSee('Batal');
    }

    /**
     * Uji 2: Modal Tambah Stok berfungsi menambah stok dan mencatat mutasi masuk.
     */
    public function test_tambah_stok_modal_submission_increases_stock_and_records_mutation(): void
    {
        $initialStock = (float) $this->pomade->current_stock;

        $response = $this->actingAs($this->manager)->post(route('inventori.stock-in', $this->pomade), [
            'quantity' => 10.5,
            'reference_type' => 'restock',
            'notes' => 'Pembelian dari supplier Faktur #INV-9921',
        ]);

        $response->assertRedirect(route('inventori.show', $this->pomade));
        $response->assertSessionHas('success');

        $this->pomade->refresh();
        $this->assertEquals($initialStock + 10.5, (float) $this->pomade->current_stock);

        $mutation = InventoryMutation::where('inventory_item_id', $this->pomade->id)
            ->where('type', InventoryMutationType::In)
            ->latest('id')
            ->first();

        $this->assertNotNull($mutation);
        $this->assertEquals(10.5, (float) $mutation->quantity);
        $this->assertEquals('restock', $mutation->reference_type);
        $this->assertEquals('Pembelian dari supplier Faktur #INV-9921', $mutation->notes);
        $this->assertEquals($this->manager->id, $mutation->user_id);
    }

    /**
     * Uji 3: Transaksi kasir berstatus Paid otomatis memotong stok bahan operasional sesuai konfigurasi.
     */
    public function test_paid_transaction_automatically_deducts_operational_inventory_stock(): void
    {
        $initialPomade = (float) $this->pomade->current_stock;
        $initialCape = (float) $this->cape->current_stock;

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-001',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->stylingService->id,
            'qty' => 2, // 2 orang styling
            'price' => 75000,
            'subtotal' => 150000,
        ]);

        // Ubah status ke Paid
        $transaction->update(['status' => StatusTransaksi::Paid]);

        $this->pomade->refresh();
        $this->cape->refresh();

        // Konfigurasi styling: 0.1 pomade per qty x 2 = 0.2
        $this->assertEquals($initialPomade - 0.2, (float) $this->pomade->current_stock);

        // Konfigurasi styling: 0.05 cape per qty x 2 = 0.1
        $this->assertEquals($initialCape - 0.1, (float) $this->cape->current_stock);

        // Pastikan mutasi tercatat dengan referensi jelas dan user petugas
        $pomadeMutation = InventoryMutation::where('inventory_item_id', $this->pomade->id)
            ->where('type', InventoryMutationType::Out)
            ->latest('id')
            ->first();

        $this->assertNotNull($pomadeMutation);
        $this->assertEquals(0.2, (float) $pomadeMutation->quantity);
        $this->assertEquals('service_usage', $pomadeMutation->reference_type);
        $this->assertStringContainsString('TRX-TEST-001', $pomadeMutation->notes);
        $this->assertEquals($this->cashier->id, $pomadeMutation->user_id);
    }

    /**
     * Uji 4: Mencegah pemotongan stok ganda untuk transaksi yang sama (idempotent).
     */
    public function test_prevents_double_stock_deduction_for_same_transaction(): void
    {
        $initialPomade = (float) $this->pomade->current_stock;

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-IDEMPOTENT',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->stylingService->id,
            'qty' => 1,
            'price' => 75000,
            'subtotal' => 75000,
        ]);

        $service = app(InventoriTransaksiService::class);

        // Panggil pertama kali
        $service->deductStockForTransaction($transaction);
        $this->pomade->refresh();
        $this->assertEquals($initialPomade - 0.1, (float) $this->pomade->current_stock);

        // Panggil kedua kali: tidak boleh memotong lagi
        $service->deductStockForTransaction($transaction);
        $this->pomade->refresh();
        $this->assertEquals($initialPomade - 0.1, (float) $this->pomade->current_stock);

        $mutationCount = InventoryMutation::where('inventory_item_id', $this->pomade->id)
            ->where('notes', 'like', '%TRX-TEST-IDEMPOTENT%')
            ->count();
        $this->assertEquals(1, $mutationCount);
    }

    /**
     * Uji 5: Mencegah stok menjadi negatif secara tidak wajar jika stok menipis/habis.
     */
    public function test_prevents_stock_from_becoming_negative(): void
    {
        // Set stok pomade tinggal 0.05
        $this->pomade->update(['current_stock' => 0.05]);

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-NON-NEGATIVE',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->stylingService->id,
            'qty' => 1, // Membutuhkan 0.1, tapi hanya ada 0.05
            'price' => 75000,
            'subtotal' => 75000,
        ]);

        app(InventoriTransaksiService::class)->deductStockForTransaction($transaction);

        $this->pomade->refresh();
        $this->assertGreaterThanOrEqual(0.0, (float) $this->pomade->current_stock);
        $this->assertEquals(0.0, (float) $this->pomade->current_stock);
    }

    /**
     * Uji 6: Transaksi yang dibatalkan atau direfund mengembalikan stok yang telah dipotong.
     */
    public function test_cancelled_or_refunded_transaction_restores_deducted_stock(): void
    {
        $initialPomade = (float) $this->pomade->current_stock;

        $transaction = Transaction::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'transaction_code' => 'TRX-TEST-CANCEL',
            'subtotal' => 75000,
            'discount' => 0,
            'total' => 75000,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $this->stylingService->id,
            'qty' => 1,
            'price' => 75000,
            'subtotal' => 75000,
        ]);

        // 1. Transaksi Paid -> stok berkurang
        $transaction->update(['status' => StatusTransaksi::Paid]);
        $this->pomade->refresh();
        $this->assertEquals($initialPomade - 0.1, (float) $this->pomade->current_stock);

        // 2. Transaksi Dibatalkan -> stok dikembalikan secara otomatis
        $transaction->update(['status' => StatusTransaksi::Cancelled]);
        $this->pomade->refresh();
        $this->assertEquals($initialPomade, (float) $this->pomade->current_stock);

        // Cek mutasi pengembalian
        $reversalMutation = InventoryMutation::where('inventory_item_id', $this->pomade->id)
            ->where('type', InventoryMutationType::In)
            ->where('reference_type', 'refund')
            ->latest('id')
            ->first();

        $this->assertNotNull($reversalMutation);
        $this->assertEquals(0.1, (float) $reversalMutation->quantity);
        $this->assertStringContainsString('Pengembalian stok pembatalan transaksi TRX-TEST-CANCEL', $reversalMutation->notes);
    }

    /**
     * Uji 7: Halaman Riwayat Stok (/inventori/riwayat) menampilkan judul, deskripsi, dan header daftar yang benar.
     */
    public function test_riwayat_stok_page_displays_correct_headers_and_descriptions(): void
    {
        $response = $this->actingAs($this->manager)->get(route('inventori.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Stok');
        $response->assertSee('Pantau seluruh aktivitas stok masuk dan keluar.');
        $response->assertSee('Daftar Riwayat Stok');
        $response->assertDontSee('Log Mutasi Stok Terdaftar');
    }
}
