<?php

namespace App\Services\KasirPOS;

use App\Enums\StatusTransaksi;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\Karyawan\KomisiKaryawanService;
use App\Services\MembershipPromo\MemberService;
use App\Services\MembershipPromo\PromoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransaksiService
{
    public function __construct(
        protected KasirService $kasirService,
        protected PembayaranService $pembayaranService,
        protected PaymentGatewayService $gatewayService,
        protected TransaksiQueryService $queryService,
        protected TransaksiSourceService $sourceService,
    ) {}

    public function getTransactionsForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryService->getTransactionsForUser($user, $filters, $perPage);
    }

    public function getTransactionDetails(User $user, Transaction $transaction): Transaction
    {
        return $this->queryService->getTransactionDetails($user, $transaction);
    }

    /**
     * Buat transaksi kasir dengan validasi shift, anti double-payment, dan promo backend.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function createTransaction(User $cashier, array $data): Transaction
    {
        $branch = Branch::findOrFail($data['branch_id']);
        $this->kasirService->authorizeBranchAccess($cashier, $branch);

        $activeShift = $this->kasirService->getActiveShift($cashier, $branch->id);
        if (! $activeShift && $cashier->role?->name === 'cashier') {
            throw ValidationException::withMessages([
                'shift' => 'Shift kasir belum dibuka. Harap buka shift kasir terlebih dahulu sebelum melayani transaksi.',
            ]);
        }

        if (! empty($data['booking_id'])) {
            $isPaid = Transaction::where('booking_id', $data['booking_id'])
                ->where('status', StatusTransaksi::Paid)
                ->exists();
            if ($isPaid) {
                throw ValidationException::withMessages([
                    'booking_id' => 'Booking ini sudah dibayar lunas dan tidak dapat ditagih ulang.',
                ]);
            }
        }

        if (! empty($data['order_id'])) {
            $isPaid = Transaction::where('order_id', $data['order_id'])
                ->where('status', StatusTransaksi::Paid)
                ->exists();
            if ($isPaid) {
                throw ValidationException::withMessages([
                    'order_id' => 'Pesanan ini sudah dibayar lunas dan tidak dapat ditagih ulang.',
                ]);
            }
        }

        return DB::transaction(function () use ($cashier, $branch, $data) {
            $items = $data['items'];
            $serviceIds = collect($items)->pluck('service_id')->unique()->toArray();
            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            $subtotal = 0;
            $detailsToCreate = [];

            foreach ($items as $item) {
                $service = $services->get($item['service_id']);
                if (! $service) {
                    throw ValidationException::withMessages(['items' => 'Layanan tidak ditemukan.']);
                }

                $qty = max(1, (float) $item['qty']);
                $itemSubtotal = (float) $service->price * $qty;
                $subtotal += $itemSubtotal;

                $detailsToCreate[] = [
                    'service_id' => $service->id,
                    'qty' => $qty,
                    'price' => (float) $service->price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discount = 0;
            $appliedPromo = null;

            if (! empty($data['promo_code'])) {
                $customerUser = ! empty($data['booking_id'])
                    ? Booking::find($data['booking_id'])?->user
                    : (! empty($data['order_id']) ? Order::find($data['order_id'])?->user : null);

                $promoVal = app(PromoService::class)->validatePromo(
                    $data['promo_code'],
                    $subtotal,
                    $branch->id,
                    $customerUser
                );

                if (! $promoVal['valid']) {
                    throw ValidationException::withMessages([
                        'promo_code' => $promoVal['message'] ?? 'Kode promo tidak valid atau syarat tidak terpenuhi.',
                    ]);
                }

                $discount = (float) ($promoVal['discount'] ?? 0);
                $appliedPromo = $promoVal['promo'] ?? null;
            } elseif (! empty($data['promotion_id'])) {
                $p = Promotion::find($data['promotion_id']);
                if ($p) {
                    $promoVal = app(PromoService::class)->validatePromo($p->code, $subtotal, $branch->id);
                    if ($promoVal['valid']) {
                        $discount = (float) ($promoVal['discount'] ?? 0);
                        $appliedPromo = $p;
                    }
                }
            } elseif (isset($data['discount'])) {
                $discount = (float) $data['discount'];
            }

            $discount = min($subtotal, max(0, $discount));
            $total = max(0, $subtotal - $discount);
            $transactionCode = 'TRX-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

            $isGateway = in_array($data['payment_method'], ['qris', 'e_wallet', 'virtual_account'], true);
            $initialStatus = $data['status'] ?? ($isGateway ? StatusTransaksi::Pending : StatusTransaksi::Paid);

            $employeeId = $data['employee_id'] ?? null;
            if (! $employeeId && ! empty($data['booking_id'])) {
                $employeeId = Booking::where('id', $data['booking_id'])->value('employee_id');
            }
            if (! $employeeId && ! empty($data['order_id'])) {
                $employeeId = Order::where('id', $data['order_id'])->value('employee_id');
            }

            $transaction = Transaction::create([
                'branch_id' => $branch->id,
                'cashier_id' => $cashier->id,
                'employee_id' => $employeeId,
                'booking_id' => $data['booking_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'transaction_code' => $transactionCode,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => $initialStatus,
                'transaction_date' => isset($data['transaction_date']) ? Carbon::parse($data['transaction_date']) : Carbon::now(),
            ]);

            foreach ($detailsToCreate as $detail) {
                TransactionDetail::create(array_merge($detail, ['transaction_id' => $transaction->id]));
            }

            if ($isGateway) {
                $this->gatewayService->initiate($transaction, [
                    'method' => $data['payment_method'],
                    'channel' => $data['payment_method'],
                    'description' => "Pembayaran transaksi {$transaction->transaction_code}",
                    'customer_name' => $transaction->booking?->user?->name
                        ?? $transaction->order?->user?->name
                        ?? ($data['customer_name'] ?? 'Pelanggan'),
                    'customer_email' => $transaction->booking?->user?->email
                        ?? $transaction->order?->user?->email,
                ]);
            } else {
                $this->pembayaranService->processPayment($transaction, [
                    'payment_method' => $data['payment_method'],
                    'amount' => $total,
                    'cash_received' => $data['cash_received'] ?? $total,
                    'reference_number' => $data['reference_number'] ?? null,
                ]);

                app(MemberService::class)->awardPointsFromTransaction($transaction);
                app(KomisiKaryawanService::class)->processTransactionCommission($transaction);
            }

            if ($appliedPromo) {
                $customerUser = $transaction->booking?->user ?? $transaction->order?->user ?? $cashier;
                app(PromoService::class)->recordPromoUsage($appliedPromo, $customerUser, $transaction);
            }

            return $transaction->load(['branch', 'cashier', 'details.service', 'payments', 'booking', 'order']);
        });
    }

    public function getEligibleBookings(int $branchId): Collection
    {
        return $this->sourceService->getEligibleBookings($branchId);
    }

    public function getEligibleOrders(int $branchId): Collection
    {
        return $this->sourceService->getEligibleOrders($branchId);
    }

    public function getPendingPayments(Branch $branch, string $typeFilter = 'all'): \Illuminate\Support\Collection
    {
        return $this->sourceService->getPendingPayments($branch, $typeFilter);
    }

    public function resolveSourceData(string $type, int|string $id): ?array
    {
        return $this->sourceService->resolveSourceData($type, $id);
    }
}
