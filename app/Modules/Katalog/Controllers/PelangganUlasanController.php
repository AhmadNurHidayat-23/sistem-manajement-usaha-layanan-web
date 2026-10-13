<?php

namespace App\Modules\Katalog\Controllers;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganUlasanController extends Controller
{
    /**
     * List customer's own reviews.
     */
    public function index(Request $request): View
    {
        $ulasans = Ulasan::with(['service', 'transaction.order.branch', 'transaction.branch'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('modules.katalog.customer.ulasan.index', compact('ulasans'));
    }

    /**
     * Form ulasan pesanan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $orderId = $request->query('order_id');

        if (! $orderId) {
            return redirect()->route('customer.pesanan.index')->with('error', 'Pilih pesanan yang akan diulas terlebih dahulu.');
        }

        $order = Order::with(['service', 'branch', 'transaction.ulasan'])
            ->where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status !== StatusPesanan::Completed) {
            return redirect()->route('customer.pesanan.show', $order)
                ->with('error', 'Ulasan hanya dapat diberikan untuk pesanan yang sudah selesai.');
        }

        // Cek transaksi
        if (! $order->transaction) {
            return redirect()->route('customer.pesanan.show', $order)
                ->with('error', 'Pesanan ini belum memiliki data transaksi untuk diulas.');
        }

        // Cek ulasan duplikat
        if ($order->transaction->ulasan || Ulasan::where('transaction_id', $order->transaction->id)->exists()) {
            $ulasan = $order->transaction->ulasan ?? Ulasan::where('transaction_id', $order->transaction->id)->first();

            return redirect()->route('customer.ulasan.edit', $ulasan)
                ->with('info', 'Pesanan ini sudah memiliki ulasan. Anda dapat mengeditnya.');
        }

        return view('modules.katalog.customer.ulasan.create', compact('order'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::with(['transaction.ulasan'])
            ->where('id', $validated['order_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status !== StatusPesanan::Completed) {
            return back()->with('error', 'Ulasan hanya bisa diberikan untuk pesanan yang sudah selesai.');
        }

        $transaction = $order->transaction;
        if (! $transaction) {
            return back()->with('error', 'Pesanan ini belum memiliki data transaksi pembayaran yang valid untuk diulas.');
        }

        if ($transaction->ulasan || Ulasan::where('transaction_id', $transaction->id)->exists()) {
            return back()->with('error', 'Pesanan ini sudah memiliki ulasan.');
        }

        Ulasan::create([
            'user_id' => $request->user()->id,
            'transaction_id' => $transaction->id,
            'service_id' => $order->service_id,
            'rating' => $validated['rating'],
            'komentar' => $validated['komentar'] ?? null,
        ]);

        return redirect()->route('customer.pesanan.show', $order)
            ->with('success', 'Ulasan berhasil dikirim. Terima kasih!');
    }

    /**
     * Edit own review.
     */
    public function edit(Request $request, Ulasan $ulasan): View|RedirectResponse
    {
        if ($ulasan->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki izin mengubah ulasan ini.');
        }

        $ulasan->load(['transaction', 'service']);

        return view('modules.katalog.customer.ulasan.edit', compact('ulasan'));
    }

    /**
     * Update own review.
     */
    public function update(Request $request, Ulasan $ulasan): RedirectResponse
    {
        if ($ulasan->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki izin mengubah ulasan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);

        $ulasan->update([
            'rating' => $validated['rating'],
            'komentar' => $validated['komentar'] ?? null,
        ]);

        return redirect()->route('customer.ulasan.index')
            ->with('success', 'Ulasan berhasil diperbarui.');
    }
}
