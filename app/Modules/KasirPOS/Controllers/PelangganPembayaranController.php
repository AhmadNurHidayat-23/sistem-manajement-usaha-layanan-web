<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganPembayaranController extends Controller
{
    /**
     * Informasi pembayaran pesanan.
     */
    public function show(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        $order->load(['branch', 'service', 'transaction.payments']);

        if (! $order->transaction) {
            return back()->with('info', 'Transaksi untuk pesanan ini belum dibuat oleh kasir.');
        }

        return view('modules.kasir-pos.customer.pembayaran.show', compact('order'));
    }
}
