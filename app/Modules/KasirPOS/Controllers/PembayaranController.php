<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Modules\KasirPOS\Requests\StorePembayaranRequest;
use App\Modules\KasirPOS\Services\PembayaranService;
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
