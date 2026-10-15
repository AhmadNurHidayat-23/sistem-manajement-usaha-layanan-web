<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasirPOS\StorePembayaranRequest;
use App\Models\Transaction;
use App\Services\KasirPOS\PembayaranService;
use Illuminate\Http\RedirectResponse;

class PembayaranController extends Controller
{
    public function __construct(
        protected PembayaranService $pembayaranService
    ) {}

    /**
     * Simpan dan proses pembayaran.
     */
    public function store(StorePembayaranRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $transaction = Transaction::findOrFail($validated['transaction_id']);
        $payment = $this->pembayaranService->processPayment($transaction, $validated);

        return redirect()->back()
            ->with('success', 'Pembayaran sebesar Rp '.number_format($payment->amount, 0, ',', '.').' berhasil diproses.');
    }
}
