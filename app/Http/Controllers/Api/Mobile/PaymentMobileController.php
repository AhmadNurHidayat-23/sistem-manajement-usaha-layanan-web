<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\KasirPOS\PaymentGatewayService;
use App\Services\MembershipPromo\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMobileController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $gatewayService,
        protected PromoService $promoService,
    ) {}

    /**
     * Buat tagihan pembayaran via Midtrans QRIS / Gateway.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', 'string', 'in:qris,e_wallet,virtual_account'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($validated['booking_id']) && empty($validated['order_id'])) {
            return response()->json(['success' => false, 'message' => 'Pilih booking atau pesanan yang akan dibayar.'], 422);
        }

        /** @var User $user */
        $user = $request->user();

        // 1. Selesaikan data transaksi
        if (! empty($validated['booking_id'])) {
            $booking = Booking::with(['service', 'branch', 'details.service', 'transaction.payments'])
                ->where('user_id', $user->id)
                ->find($validated['booking_id']);

            if (! $booking) {
                return response()->json(['success' => false, 'message' => 'Data booking tidak ditemukan atau bukan milik Anda.'], 404);
            }
            if ($booking->transaction && $booking->transaction->status === StatusTransaksi::Paid) {
                return response()->json(['success' => false, 'message' => 'Booking ini sudah dibayar lunas.'], 422);
            }

            $subtotal = (float) $booking->service->price;
            foreach ($booking->details as $d) {
                $subtotal += (float) ($d->price ?? 0);
            }
            $branchId = $booking->branch_id;
            $serviceId = $booking->service_id;
            $serviceQty = 1;
            $servicePrice = (float) $booking->service->price;
            $existingTransaction = $booking->transaction;
            $bookingId = $booking->id;
            $orderId = null;
        } else {
            $order = Order::with(['service', 'branch', 'details.service', 'transaction.payments'])
                ->where('user_id', $user->id)
                ->find($validated['order_id']);

            if (! $order) {
                return response()->json(['success' => false, 'message' => 'Data pesanan tidak ditemukan atau bukan milik Anda.'], 404);
            }
            if ($order->transaction && $order->transaction->status === StatusTransaksi::Paid) {
                return response()->json(['success' => false, 'message' => 'Pesanan ini sudah dibayar lunas.'], 422);
            }
            if ($order->service?->isWeightBased() && $order->estimated_quantity === null && $order->details->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Pesanan berbasis berat harus ditimbang terlebih dahulu oleh petugas cabang.'], 422);
            }

            $serviceQty = (float) ($order->estimated_quantity ?? 1);
            $servicePrice = (float) $order->service->price;
            $subtotal = $serviceQty * $servicePrice;
            foreach ($order->details as $d) {
                $subtotal += ((float) ($d->price ?? 0)) * ((float) ($d->qty ?? 1));
            }
            $branchId = $order->branch_id;
            $serviceId = $order->service_id;
            $existingTransaction = $order->transaction;
            $bookingId = null;
            $orderId = $order->id;
        }

        // 2. Hitung diskon promo jika ada (backend source of truth)
        $discount = 0.0;
        if (! empty($validated['promo_code'])) {
            $promoCheck = $this->promoService->validatePromo($validated['promo_code'], $subtotal, $branchId, $user);
            if (! $promoCheck['valid']) {
                return response()->json(['success' => false, 'message' => $promoCheck['message'] ?? 'Promo tidak valid.'], 422);
            }
            $discount = (float) $promoCheck['discount'];
        }

        $total = max(0, $subtotal - $discount);

        // 3. Simpan / perbarui transaksi
        if ($existingTransaction) {
            $transaction = $existingTransaction;
            $transaction->update(['subtotal' => $subtotal, 'discount' => $discount, 'total' => $total]);
        } else {
            $transaction = Transaction::create([
                'branch_id' => $branchId,
                'cashier_id' => $user->id,
                'booking_id' => $bookingId,
                'order_id' => $orderId,
                'transaction_code' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => StatusTransaksi::Pending,
                'transaction_date' => now(),
            ]);

            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'service_id' => $serviceId,
                'qty' => $serviceQty,
                'price' => $servicePrice,
                'subtotal' => $subtotal,
            ]);
        }

        // 4. Inisiasi Midtrans Sandbox QRIS via PaymentGatewayService
        try {
            $payment = $this->gatewayService->initiate($transaction, [
                'method' => $validated['payment_method'],
                'channel' => $validated['payment_method'],
                'description' => "Pembayaran transaksi {$transaction->transaction_code}",
                'customer_name' => $user->name,
                'customer_email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menginisiasi pembayaran: '.$e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tagihan pembayaran berhasil dibuat.',
            'data' => [
                'id' => $payment->id,
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'amount' => (float) $payment->amount,
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'gateway_reference' => $payment->gateway_reference,
                'qr_url' => $payment->gateway_payload['qr_url'] ?? null,
                'expired_at' => $payment->expired_at?->toIso8601String(),
                'created_at' => $payment->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Detail satu pembayaran.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $payment = $this->findOwnedPayment($id, $user);

        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'transaction_code' => $payment->transaction?->transaction_code,
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'gateway_reference' => $payment->gateway_reference,
                'qr_url' => $payment->gateway_payload['qr_url'] ?? null,
                'is_paid' => $payment->isPaid(),
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'expired_at' => $payment->expired_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Polling status pembayaran terbaru.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $payment = $this->findOwnedPayment($id, $user);

        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda.'], 404);
        }

        if (! $payment->status->isFinal()) {
            try {
                $payment = $this->gatewayService->checkStatus($payment);
            } catch (\Throwable) {
                // Gunakan status lokal jika koneksi gateway error saat polling
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'is_paid' => $payment->isPaid(),
                'is_final' => $payment->status->isFinal(),
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Simulasi pembayaran berhasil (dibatasi hanya pada automated testing terisolasi).
     */
    public function simulate(Request $request, int $id): JsonResponse
    {
        abort_unless(app()->environment('testing'), 403, 'Endpoint simulasi dinonaktifkan.');

        /** @var User $user */
        $user = $request->user();
        $payment = $this->findOwnedPayment($id, $user);

        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Data pembayaran tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $payment = $this->gatewayService->simulateSuccess($payment);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil disimulasikan [Sandbox].',
            'data' => [
                'id' => $payment->id,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'is_paid' => true,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Cari pembayaran milik pengguna saat ini.
     */
    protected function findOwnedPayment(int $id, User $user): ?Payment
    {
        return Payment::with(['transaction.booking', 'transaction.order'])
            ->whereHas('transaction', function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->whereHas('booking', fn ($b) => $b->where('user_id', $user->id))
                        ->orWhereHas('order', fn ($o) => $o->where('user_id', $user->id))
                        ->orWhere('cashier_id', $user->id);
                });
            })
            ->find($id);
    }
}
