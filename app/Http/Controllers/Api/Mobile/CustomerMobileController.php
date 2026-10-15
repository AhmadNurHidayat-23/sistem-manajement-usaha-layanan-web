<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\StatusTransaksi;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\BookingAntrian\BookingService;
use App\Services\MembershipPromo\MemberService;
use App\Services\MembershipPromo\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerMobileController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PromoService $promoService,
    ) {}

    /**
     * Daftar booking / janji temu pelanggan.
     */
    public function bookings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $filters = $request->only(['status', 'date']);
        $bookings = $this->bookingService->getBookingsForUser($user, $filters, 30);

        $items = collect($bookings->items())->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_date' => $booking->booking_date?->format('Y-m-d'),
                'booking_time' => $booking->booking_time,
                'status' => $booking->status->value,
                'status_label' => $booking->status->label(),
                'branch' => $booking->branch ? [
                    'id' => $booking->branch->id,
                    'name' => $booking->branch->name,
                    'address' => $booking->branch->address,
                ] : null,
                'service' => $booking->service ? [
                    'id' => $booking->service->id,
                    'name' => $booking->service->name,
                    'price' => (float) $booking->service->price,
                    'duration_formatted' => $booking->service->formatted_duration,
                ] : null,
                'employee' => $booking->employee ? [
                    'id' => $booking->employee->id,
                    'name' => $booking->employee->name,
                ] : null,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Slot ketersediaan booking untuk mobile.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'date' => ['required', 'date'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        if (! $branch->isOpenOnDate($validated['date'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is_open' => false,
                    'message' => 'Cabang tutup pada tanggal ini.',
                    'slots' => [],
                    'slots_detailed' => [],
                ],
            ]);
        }

        $slots = $this->bookingService->getAvailableSlots(
            $branch,
            $validated['date'],
            null,
            $validated['employee_id'] ?? null,
            $validated['service_id'] ?? null,
            $validated['extra_service_ids'] ?? []
        );

        $slotsDetailed = $this->bookingService->getSlotsWithStatus(
            $branch,
            $validated['date'],
            null,
            $validated['employee_id'] ?? null,
            $validated['service_id'] ?? null,
            $validated['extra_service_ids'] ?? []
        );

        return response()->json([
            'success' => true,
            'data' => [
                'is_open' => true,
                'slots' => $slots,
                'slots_detailed' => $slotsDetailed,
            ],
        ]);
    }

    /**
     * Buat booking / janji temu baru.
     */
    public function storeBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['integer', 'exists:services,id'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ]);

        // STRICT VALIDATION: service harus aktif dan milik branch_id tersebut
        $service = Service::where('is_active', true)->find($validated['service_id']);
        if (! $service || (int) $service->branch_id !== (int) $validated['branch_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan yang dipilih tidak tersedia pada cabang tersebut.',
            ], 422);
        }

        // STRICT VALIDATION: cabang harus melayani appointment
        $branch = Branch::where('is_active', true)->find($validated['branch_id']);
        if (! $branch || ! in_array($branch->service_mode, ['appointment', 'both'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang yang dipilih tidak melayani booking janji temu (appointment).',
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();

        // Hitung subtotal
        $subtotal = (float) $service->price;
        if (! empty($validated['extra_service_ids'])) {
            $extraPrices = Service::whereIn('id', $validated['extra_service_ids'])->sum('price');
            $subtotal += (float) $extraPrices;
        }

        // Validasi promo backend (source of truth)
        $discount = 0.0;
        $promoCode = null;
        if (! empty($validated['promo_code'])) {
            $promoCheck = $this->promoService->validatePromo($validated['promo_code'], $subtotal, (int) $validated['branch_id'], $user);
            if (! $promoCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $promoCheck['message'] ?? 'Promo tidak valid.',
                ], 422);
            }
            $discount = (float) $promoCheck['discount'];
            $promoCode = $validated['promo_code'];
        }

        $total = max(0, $subtotal - $discount);

        try {
            $result = DB::transaction(function () use ($user, $validated, $service, $subtotal, $discount, $total) {
                $booking = $this->bookingService->createBooking($user, $validated);

                $transaction = Transaction::create([
                    'branch_id' => (int) $validated['branch_id'],
                    'cashier_id' => $user->id,
                    'booking_id' => $booking->id,
                    'transaction_code' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => StatusTransaksi::Pending,
                    'transaction_date' => now(),
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'service_id' => $service->id,
                    'qty' => 1,
                    'price' => (float) $service->price,
                    'subtotal' => (float) $service->price,
                ]);

                return [$booking, $transaction];
            });

            /** @var Booking $booking */
            [$booking, $transaction] = $result;

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat. Menunggu konfirmasi petugas.',
                'data' => [
                    'id' => $booking->id,
                    'booking_date' => $booking->booking_date?->format('Y-m-d'),
                    'booking_time' => $booking->booking_time,
                    'status' => $booking->status->value,
                    'status_label' => $booking->status->label(),
                    'promo_code' => $promoCode,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'branch' => [
                        'id' => $booking->branch?->id,
                        'name' => $booking->branch?->name,
                    ],
                    'service' => [
                        'id' => $booking->service?->id,
                        'name' => $booking->service?->name,
                        'price' => (float) ($booking->service?->price ?? 0),
                        'duration_formatted' => $booking->service?->formatted_duration,
                    ],
                ],
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detail satu booking pelanggan.
     */
    public function showBooking(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $booking = Booking::with(['branch', 'service', 'employee', 'details.service', 'transaction.payments'])
            ->where('user_id', $user->id)
            ->find($id);

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Data booking tidak ditemukan.',
            ], 404);
        }

        $latestPayment = $booking->transaction?->payments()->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->id,
                'booking_date' => $booking->booking_date?->format('Y-m-d'),
                'booking_time' => $booking->booking_time,
                'status' => $booking->status->value,
                'status_label' => $booking->status->label(),
                'branch' => [
                    'id' => $booking->branch?->id,
                    'name' => $booking->branch?->name,
                    'address' => $booking->branch?->address,
                    'phone' => $booking->branch?->phone,
                ],
                'service' => [
                    'id' => $booking->service?->id,
                    'name' => $booking->service?->name,
                    'price' => (float) ($booking->service?->price ?? 0),
                    'duration_formatted' => $booking->service?->formatted_duration,
                ],
                'employee' => $booking->employee ? [
                    'id' => $booking->employee->id,
                    'name' => $booking->employee->name,
                ] : null,
                'notes' => $booking->notes,
                'extra_services' => $booking->details->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->service?->name,
                    'price' => (float) ($d->price ?? 0),
                ]),
                'payment' => $latestPayment ? [
                    'id' => $latestPayment->id,
                    'status' => $latestPayment->status->value,
                    'status_label' => $latestPayment->status->label(),
                    'amount' => (float) $latestPayment->amount,
                    'payment_method' => $latestPayment->payment_method,
                    'qr_url' => $latestPayment->gateway_payload['qr_url'] ?? null,
                ] : null,
                'payment_status' => $booking->transaction ? $booking->transaction->status->value : 'unpaid',
                'payment_status_label' => $booking->transaction ? $booking->transaction->status->label() : 'Belum Dibayar',
                'total_amount' => (float) ($booking->transaction?->total ?? $booking->service?->price ?? 0),
                'discount_amount' => (float) ($booking->transaction?->discount ?? 0),
                'created_at' => $booking->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Daftar pesanan drop-off (laundry/detailing dsb) pelanggan.
     */
    public function orders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $filters = $request->only(['status']);
        $orders = $this->bookingService->getOrdersForUser($user, $filters, 30);

        $items = collect($orders->items())->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'estimated_quantity' => $order->estimated_quantity ? (float) $order->estimated_quantity : null,
                'pickup_option' => (bool) $order->pickup_option,
                'estimated_finish_at' => $order->estimated_finish_at?->toIso8601String(),
                'branch' => $order->branch ? [
                    'id' => $order->branch->id,
                    'name' => $order->branch->name,
                    'address' => $order->branch->address,
                ] : null,
                'service' => $order->service ? [
                    'id' => $order->service->id,
                    'name' => $order->service->name,
                    'price' => (float) $order->service->price,
                    'unit' => $order->service->unit ?? 'kg',
                    'is_weight_based' => $order->service->isWeightBased(),
                    'duration_formatted' => $order->service->formatted_duration,
                ] : null,
                'notes' => $order->notes,
                'created_at' => $order->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Buat pesanan drop-off baru (laundry / perawatan sepatu / detailing dsb).
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'pickup_option' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'estimated_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'extra_service_ids' => ['nullable', 'array'],
            'extra_service_ids.*' => ['integer', 'exists:services,id'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ]);

        // STRICT VALIDATION: service harus aktif dan milik branch_id tersebut
        $service = Service::where('is_active', true)->find($validated['service_id']);
        if (! $service || (int) $service->branch_id !== (int) $validated['branch_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan yang dipilih tidak tersedia pada cabang tersebut.',
            ], 422);
        }

        // STRICT VALIDATION: cabang harus melayani drop-off
        $branch = Branch::where('is_active', true)->find($validated['branch_id']);
        if (! $branch || ! in_array($branch->service_mode, ['drop_off', 'both'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang yang dipilih tidak melayani pesanan drop-off.',
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();

        $qty = (float) ($validated['estimated_quantity'] ?? 1);
        $subtotal = $qty * (float) $service->price;
        if (! empty($validated['extra_service_ids'])) {
            $extraPrices = Service::whereIn('id', $validated['extra_service_ids'])->sum('price');
            $subtotal += (float) $extraPrices;
        }

        // Validasi promo backend (source of truth)
        $discount = 0.0;
        $promoCode = null;
        if (! empty($validated['promo_code'])) {
            $promoCheck = $this->promoService->validatePromo($validated['promo_code'], $subtotal, (int) $validated['branch_id'], $user);
            if (! $promoCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $promoCheck['message'] ?? 'Promo tidak valid.',
                ], 422);
            }
            $discount = (float) $promoCheck['discount'];
            $promoCode = $validated['promo_code'];
        }

        $total = max(0, $subtotal - $discount);

        $order = $this->bookingService->createOrder($user, $validated);

        $transaction = Transaction::create([
            'branch_id' => (int) $validated['branch_id'],
            'cashier_id' => $user->id,
            'order_id' => $order->id,
            'transaction_code' => 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'status' => StatusTransaksi::Pending,
            'transaction_date' => now(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'qty' => $qty,
            'price' => (float) $service->price,
            'subtotal' => $subtotal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat. Silakan serahkan barang ke cabang pilihan.',
            'data' => [
                'id' => $order->id,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'promo_code' => $promoCode,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'estimated_quantity' => $order->estimated_quantity ? (float) $order->estimated_quantity : null,
                'estimated_finish_at' => $order->estimated_finish_at?->toIso8601String(),
                'branch' => [
                    'id' => $order->branch?->id,
                    'name' => $order->branch?->name,
                ],
                'service' => [
                    'id' => $order->service?->id,
                    'name' => $order->service?->name,
                    'price' => (float) ($order->service?->price ?? 0),
                    'unit' => $order->service?->unit ?? 'kg',
                    'is_weight_based' => $order->service?->isWeightBased(),
                    'duration_formatted' => $order->service?->formatted_duration,
                ],
            ],
        ], 201);
    }

    /**
     * Detail satu pesanan drop-off pelanggan.
     */
    public function showOrder(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $order = Order::with(['branch', 'service', 'details.service', 'transaction.payments'])
            ->where('user_id', $user->id)
            ->find($id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Data pesanan tidak ditemukan.',
            ], 404);
        }

        $latestPayment = $order->transaction?->payments()->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'estimated_quantity' => $order->estimated_quantity ? (float) $order->estimated_quantity : null,
                'pickup_option' => (bool) $order->pickup_option,
                'estimated_finish_at' => $order->estimated_finish_at?->toIso8601String(),
                'branch' => [
                    'id' => $order->branch?->id,
                    'name' => $order->branch?->name,
                    'address' => $order->branch?->address,
                    'phone' => $order->branch?->phone,
                ],
                'service' => [
                    'id' => $order->service?->id,
                    'name' => $order->service?->name,
                    'price' => (float) ($order->service?->price ?? 0),
                    'unit' => $order->service?->unit ?? 'kg',
                    'is_weight_based' => $order->service?->isWeightBased(),
                    'duration_formatted' => $order->service?->formatted_duration,
                ],
                'notes' => $order->notes,
                'extra_services' => $order->details->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->service?->name,
                    'price' => (float) ($d->price ?? 0),
                ]),
                'payment' => $latestPayment ? [
                    'id' => $latestPayment->id,
                    'status' => $latestPayment->status->value,
                    'status_label' => $latestPayment->status->label(),
                    'amount' => (float) $latestPayment->amount,
                    'payment_method' => $latestPayment->payment_method,
                    'qr_url' => $latestPayment->gateway_payload['qr_url'] ?? null,
                ] : null,
                'payment_status' => $order->transaction ? $order->transaction->status->value : 'unpaid',
                'payment_status_label' => $order->transaction ? $order->transaction->status->label() : 'Belum Dibayar',
                'total_amount' => (float) ($order->transaction?->total ?? ($order->estimated_quantity ? $order->estimated_quantity * ($order->service?->price ?? 0) : $order->service?->price ?? 0)),
                'discount_amount' => (float) ($order->transaction?->discount ?? 0),
                'created_at' => $order->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Promo yang tersedia untuk pelanggan.
     */
    public function promos(Request $request): JsonResponse
    {
        $query = Promotion::with('branch')
            ->where('status', 'active')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString());

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        $promos = $query->orderBy('end_date')->get();

        $data = $promos->map(function ($promo) {
            return [
                'id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'discount_type' => $promo->discount_type->value,
                'discount_value' => (float) $promo->discount_value,
                'quota' => $promo->quota,
                'start_date' => $promo->start_date?->format('Y-m-d'),
                'end_date' => $promo->end_date?->format('Y-m-d'),
                'branch_id' => $promo->branch_id,
                'branch_name' => $promo->branch?->name ?? 'Semua Cabang',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Informasi keanggotaan dan poin loyalty pelanggan.
     */
    public function membership(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $membership = Membership::with(['loyaltyPoint', 'loyaltyTransactions' => fn ($q) => $q->latest()->limit(10)])
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Anda belum terdaftar sebagai member.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $membership->id,
                'member_code' => $membership->member_code,
                'joined_at' => $membership->joined_at?->format('Y-m-d'),
                'status' => $membership->status->value,
                'points' => $membership->loyaltyPoint?->points ?? 0,
                'recent_transactions' => $membership->loyaltyTransactions->map(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->transaction_type,
                    'points' => $t->points,
                    'description' => $t->description,
                    'created_at' => $t->created_at?->format('Y-m-d H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Mendaftar sebagai member mandiri untuk pelanggan.
     */
    public function registerMembership(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $membership = app(MemberService::class)->registerSelfAsMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran member berhasil.',
            'data' => [
                'id' => $membership->id,
                'member_code' => $membership->member_code,
                'joined_at' => $membership->joined_at?->format('Y-m-d'),
                'status' => $membership->status->value,
                'points' => $membership->loyaltyPoint?->points ?? 0,
            ],
        ], 201);
    }
}
