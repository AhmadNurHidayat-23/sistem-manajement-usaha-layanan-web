<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Modules\KasirPOS\Requests\StoreTransaksiRequest;
use App\Modules\KasirPOS\Services\KasirService;
use App\Modules\KasirPOS\Services\TransaksiService;
use App\Services\AktivitasService;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function __construct(
        protected TransaksiService $transaksiService,
        protected KasirService $kasirService,
        protected AktivitasService $aktivitasService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Daftar transactions (Riwayat Transaksi).
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['branch_id', 'status', 'payment_method', 'start_date', 'end_date', 'q']);
        $transactions = $this->transaksiService->getTransactionsForUser($request->user(), $filters);
        $branches = $this->kasirService->getAccessibleBranches($request->user());

        return view('modules.kasir-pos.kasir.transaksi.index', compact('transactions', 'branches', 'filters'));
    }

    /**
     * Simpan transaction from POS terminal baru.
     */
    public function store(StoreTransaksiRequest $request): RedirectResponse
    {
        $transaction = $this->transaksiService->createTransaction(
            $request->user(),
            $request->validated()
        );

        $this->aktivitasService->catatDariRequest($request, 'Buat Transaksi', 'transaksi', "Transaksi '{$transaction->transaction_code}' total Rp ".number_format((float) $transaction->total, 0, ',', '.').' diproses.');

        // Periksa apakah metode pembayaran gateway (QRIS / VA / E-Wallet) dan belum Paid
        $latestPayment = $transaction->payments()->latest()->first();

        if ($latestPayment && $latestPayment->isGatewayMethod() && ! $latestPayment->isPaid()) {
            // KHUSUS QRIS: QR tampil langsung di SIMAUL, jangan redirect ke Midtrans Snap
            if ($latestPayment->payment_method === 'qris') {
                return redirect()->route('kasir.pembayaran', $transaction);
            }

            $redirectUrl = $latestPayment->gateway_payload['redirect_url'] ?? null;
            if ($redirectUrl) {
                return redirect()->away($redirectUrl);
            }

            return redirect()->route('kasir.pembayaran', $transaction);
        }

        // Kirim struk WhatsApp jika transaksi sudah lunas (misal tunai)
        rescue(fn () => $this->notifikasiService->kirimStrukTransaksi($transaction));

        return redirect()->route('pos.transactions.receipt', $transaction)
            ->with('success', "Transaksi '{$transaction->transaction_code}' berhasil diproses.");
    }

    /**
     * Tampilkan detail transaction details.
     */
    public function show(Request $request, Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction = $this->transaksiService->getTransactionDetails($request->user(), $transaction);

        return view('modules.kasir-pos.kasir.transaksi.show', compact('transaction'));
    }

    /**
     * Tampilan struk transaksi.
     */
    public function receipt(Request $request, Transaction $transaction): View|RedirectResponse
    {
        $this->authorize('receipt', $transaction);

        // Struk HANYA boleh tampil jika pembayaran sudah Paid
        $pendingPayment = $transaction->payments()
            ->whereIn('payment_method', ['qris', 'e_wallet', 'virtual_account'])
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($transaction->status !== StatusTransaksi::Paid) {
            if ($pendingPayment) {
                $redirectUrl = $pendingPayment->gateway_payload['redirect_url'] ?? null;
                if ($redirectUrl && $request->boolean('to_gateway')) {
                    return redirect()->away($redirectUrl);
                }

                return redirect()->route('kasir.pembayaran', $transaction)
                    ->with('info', 'Pembayaran sedang menunggu konfirmasi.');
            }

            return redirect()->route('kasir.pembayaran.show', $transaction)
                ->with('error', 'Struk hanya dapat diakses untuk transaksi yang sudah lunas.');
        }

        // Canonical public identifier redirect jika diakses lewat ID atau kode bisnis mentah
        $rawParam = (string) $request->route()->originalParameter('transaction');
        if (! empty($transaction->uuid) && $rawParam !== '' && $rawParam !== $transaction->uuid && ! Str::isUuid($rawParam)) {
            return redirect()->route('pos.transactions.receipt', $transaction);
        }

        $transaction = $this->transaksiService->getTransactionDetails($request->user(), $transaction);

        return view('modules.kasir-pos.kasir.struk.index', compact('transaction'));
    }

    /**
     * Kirim struk transaksi secara digital ke WhatsApp pelanggan.
     */
    public function sendReceipt(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('receipt', $transaction);

        if ($transaction->status !== StatusTransaksi::Paid) {
            return back()->with('error', 'Struk hanya dapat dikirim untuk transaksi yang sudah lunas.');
        }

        $log = $this->notifikasiService->kirimStrukTransaksi($transaction);

        if (! $log) {
            return back()->with('error', 'Nomor telepon pelanggan belum tercatat atau tidak valid.');
        }

        if ($log->status === 'sent') {
            return back()->with('success', "Struk digital berhasil dikirim ke nomor {$log->recipient} via WhatsApp.");
        }

        return back()->with('error', 'Gagal mengirim struk via WhatsApp: '.($log->error ?: 'Layanan gateway pesan sedang tidak tersedia.'));
    }
}
