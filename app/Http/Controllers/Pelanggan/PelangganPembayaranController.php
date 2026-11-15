<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        return view('pelanggan.pembayaran.show', compact('order'));
    }

    /**
     * Pelanggan memperbarui pilihan metode penyerahan barang (ambil di toko / antar ke alamat).
     */
    public function updateDeliveryMethod(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'delivery_method' => ['required', 'string', 'in:store_pickup,address_delivery'],
        ]);

        $order->delivery_method = $validated['delivery_method'];
        $order->delivery_selected_at = now();

        if ($validated['delivery_method'] === 'store_pickup') {
            $order->handover_status = $order->isPaid() ? 'ready_for_pickup' : 'waiting_pickup';
        } else {
            $order->handover_status = $order->isPaid() ? 'ready_for_delivery' : 'waiting_payment';
        }

        $order->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pilihan penyerahan barang berhasil diperbarui.',
                'delivery_method' => $order->delivery_method,
                'handover_status' => $order->handover_status,
                'is_paid' => $order->isPaid(),
            ]);
        }

        return back()->with('success', 'Pilihan penyerahan barang berhasil diperbarui.');
    }

    /**
     * Bayar online via Payment Gateway Midtrans / Sandbox.
     */
    public function payOnline(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        if ($order->isPaid()) {
            return redirect()->route('customer.pesanan.detail')
                ->with('info', 'Pesanan ini sudah dibayar lunas.');
        }

        $order->loadMissing(['service', 'branch', 'details.service', 'transaction']);

        $totalBill = $order->calculateBill();
        if ($totalBill <= 0) {
            return back()->with('error', 'Total tagihan belum dapat dihitung.');
        }

        $transaction = $order->transaction;

        if ($transaction) {
            if ((float) $transaction->total !== (float) $totalBill) {
                $transaction->update([
                    'subtotal' => $totalBill,
                    'total' => $totalBill,
                ]);
            }
        } else {
            $transaction = Transaction::create([
                'uuid' => (string) Str::uuid(),
                'branch_id' => $order->branch_id,
                'cashier_id' => $request->user()->id,
                'order_id' => $order->id,
                'transaction_code' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'subtotal' => $totalBill,
                'discount' => 0,
                'total' => $totalBill,
                'status' => StatusTransaksi::Pending,
                'transaction_date' => now(),
            ]);

            $qty = $order->isWeightBased()
                ? (float) ($order->actual_weight ?: $order->estimated_quantity ?: 1)
                : (float) ($order->estimated_quantity ?: 1);

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'service_id' => $order->service_id,
                'qty' => $qty,
                'price' => (float) ($order->service?->price ?? 0),
                'subtotal' => round((float) ($order->service?->price ?? 0) * $qty, 2),
            ]);
        }

        return redirect()->route('payment.gateway.show', $transaction->uuid);
    }
}
