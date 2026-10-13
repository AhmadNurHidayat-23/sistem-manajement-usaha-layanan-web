<?php

namespace App\Modules\KasirPOS\Controllers;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Modules\KasirPOS\Services\KasirService;
use App\Modules\KasirPOS\Services\PaymentGatewayService;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gatewayService,
        protected KasirService $kasirService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Resolusi transaksi dan pembayaran dari identitas publik yang aman.
     *
     * @return array{0: Transaction, 1: ?Payment}
     */
    protected function resolveTransactionAndPayment(mixed $identifier): array
    {
        if ($identifier instanceof Transaction) {
            $transaction = $identifier;
            $payment = $transaction->payments()->whereIn('payment_method', ['qris', 'e_wallet', 'virtual_account'])->latest()->first()
                ?? $transaction->payments()->latest()->first();

            return [$transaction, $payment];
        }

        if ($identifier instanceof Payment) {
            $payment = $identifier;
            $payment->loadMissing(['transaction.branch', 'transaction.booking.user', 'transaction.order.user']);

            return [$payment->transaction, $payment];
        }

        $code = (string) $identifier;

        // 1. Cari berdasarkan UUID, kode transaksi resmi (unik, publik, aman), atau ID transaksi
        $transaction = Transaction::where('uuid', $code)
            ->orWhere('transaction_code', $code)
            ->with(['branch', 'cashier', 'booking.user', 'order.user', 'details.service'])
            ->first();

        if (! $transaction && is_numeric($code)) {
            $transaction = Transaction::with(['branch', 'cashier', 'booking.user', 'order.user', 'details.service'])
                ->find($code);
        }

        if ($transaction) {
            $payment = $transaction->payments()->whereIn('payment_method', ['qris', 'e_wallet', 'virtual_account'])->latest()->first()
                ?? $transaction->payments()->latest()->first();

            return [$transaction, $payment];
        }

        // 2. Cari Payment berdasarkan gateway_reference atau reference_number
        $payment = Payment::where('gateway_reference', $code)
            ->orWhere('reference_number', $code)
            ->with(['transaction.branch', 'transaction.booking.user', 'transaction.order.user', 'transaction.details.service'])
            ->first();

        // 3. Fallback ID numerik legacy
        if (! $payment && is_numeric($code)) {
            $payment = Payment::with(['transaction.branch', 'transaction.booking.user', 'transaction.order.user', 'transaction.details.service'])
                ->find($code);
        }

        if ($payment && $payment->transaction) {
            return [$payment->transaction, $payment];
        }

        abort(404, 'Transaksi pembayaran tidak ditemukan.');
    }

    /**
     * Otorisasi kepemilikan dan kewenangan akses transaksi.
     */
    protected function authorizeAccess(Request $request, Transaction $transaction): void
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $roleName = $user->role?->name;

        if ($roleName === 'cashier') {
            if ($transaction->cashier_id && (int) $transaction->cashier_id === (int) $user->id) {
                return;
            }
            if ($transaction->branch) {
                $this->kasirService->authorizeBranchAccess($user, $transaction->branch);
            }
        } elseif ($roleName === 'customer') {
            $isOwner = ($transaction->booking?->user_id === $user->id)
                || ($transaction->order?->user_id === $user->id);

            if (! $isOwner) {
                abort(403, 'Anda tidak berhak mengakses transaksi pembayaran ini.');
            }
        } elseif (in_array($roleName, ['owner', 'manager'], true)) {
            $this->kasirService->authorizeBranchAccess($user, $transaction->branch);
        }
    }

    /**
     * Pilihan metode pembayaran gateway (Ganti Metode).
     */
    public function show(Request $request, mixed $identifier): View|RedirectResponse
    {
        [$transaction, $pendingPayment] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        if ($transaction->status === StatusTransaksi::Paid) {
            return redirect()->route('kasir.struk', $transaction)
                ->with('info', 'Transaksi ini telah lunas.');
        }

        return view('modules.kasir-pos.kasir.pembayaran.gateway', compact('transaction', 'pendingPayment'));
    }

    /**
     * Mulai pembayaran via gateway.
     */
    public function initiate(Request $request, mixed $identifier): RedirectResponse
    {
        [$transaction] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        $request->validate([
            'method' => ['required', 'in:qris,e_wallet,virtual_account'],
            'channel' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $payment = $this->gatewayService->initiate($transaction, [
                'method' => $request->input('method'),
                'channel' => $request->input('channel'),
                'description' => "Pembayaran transaksi {$transaction->transaction_code}",
                'customer_name' => $transaction->booking?->user?->name
                    ?? $transaction->order?->user?->name
                    ?? 'Pelanggan',
                'customer_email' => $transaction->booking?->user?->email
                    ?? $transaction->order?->user?->email,
            ]);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // KHUSUS QRIS: QR tampil langsung di SIMAUL, jangan redirect ke Midtrans Snap
        if ($payment->payment_method === 'qris') {
            return redirect()->route('kasir.pembayaran', $transaction);
        }

        $redirectUrl = $payment->gateway_payload['redirect_url'] ?? null;
        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }

        return redirect()->route('kasir.pembayaran', $transaction);
    }

    /**
     * Halaman instruksi status / menunggu pembayaran.
     */
    public function waiting(Request $request, mixed $identifier): View|RedirectResponse
    {
        [$transaction, $payment] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        if (! $payment) {
            return redirect()->route('kasir.pembayaran.show', $transaction);
        }

        $payment->loadMissing('transaction.branch');

        return view('modules.kasir-pos.kasir.pembayaran.waiting', compact('payment', 'transaction'));
    }

    /**
     * Redirect route legacy /payment/waiting/{id} ke URL semantik aman.
     */
    public function waitingRedirect(Request $request, mixed $identifier): RedirectResponse
    {
        [$transaction] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        return redirect()->route('kasir.pembayaran', $transaction);
    }

    /**
     * Cek status pembayaran.
     */
    public function poll(Request $request, mixed $identifier): JsonResponse
    {
        [$transaction, $payment] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        if (! $payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $payment = $this->gatewayService->checkStatus($payment);

        return response()->json([
            'status' => $payment->status->value,
            'label' => $payment->status->label(),
            'is_paid' => $payment->isPaid(),
            'is_final' => $payment->status->isFinal(),
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'receipt_url' => $payment->isPaid()
                ? route('kasir.struk', $transaction)
                : null,
        ]);
    }

    /**
     * Simulate a successful payment (sandbox only).
     */
    public function simulateSuccess(Request $request, mixed $identifier): RedirectResponse
    {
        abort_unless(app()->environment('testing'), 403, 'Simulasi pembayaran dinonaktifkan.');

        [$transaction, $payment] = $this->resolveTransactionAndPayment($identifier);
        $this->authorizeAccess($request, $transaction);

        if (! $payment) {
            return back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        try {
            $this->gatewayService->simulateSuccess($payment);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        rescue(fn () => $this->notifikasiService->notifikasiStatusPembayaran($payment->fresh()));

        return redirect()->route('pos.transactions.receipt', $transaction)
            ->with('success', 'Pembayaran [SANDBOX] berhasil disimulasikan.');
    }

    /**
     * Webhook callback payment gateway.
     */
    public function webhook(Request $request): Response
    {
        $payload = $request->all();

        try {
            $gatewayRef = $payload['order_id'] ?? $payload['gateway_reference'] ?? null;
            $previousPayment = $gatewayRef ? Payment::where('gateway_reference', $gatewayRef)->first() : null;
            $wasAlreadyFinal = $previousPayment?->status?->isFinal() ?? false;

            $payment = $this->gatewayService->handleWebhook($payload);

            if (! $wasAlreadyFinal) {
                if ($payment->isPaid()) {
                    rescue(fn () => $this->notifikasiService->notifikasiStatusPembayaran($payment->fresh()));
                } elseif ($payment->status === StatusPembayaran::Failed || $payment->status === StatusPembayaran::Expired) {
                    rescue(fn () => $this->notifikasiService->notifikasiStatusPembayaran($payment->fresh()));
                }
            }
        } catch (\Throwable $e) {
            Log::error('[Gateway] Webhook error: '.$e->getMessage(), [
                'payload' => $payload,
            ]);

            return response('WEBHOOK_ERROR', 200);
        }

        return response('OK', 200);
    }
}
