<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use App\Enums\StatusTransaksi;
use App\Services\WhatsAppService;
use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['uuid', 'branch_id', 'cashier_id', 'employee_id', 'booking_id', 'order_id', 'transaction_code', 'subtotal', 'discount', 'total', 'status', 'transaction_date'])]
class Transaction extends Model
{
    use HasFactory, HasPublicUuid;

    /**
     * Resolusi route binding dengan fallback kompatibilitas ke ID numerik dan kode transaksi.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        $query = $this->where($field, $value);

        if (is_numeric($value)) {
            $query->orWhere($this->getKeyName(), (int) $value);
        }

        $query->orWhere('transaction_code', (string) $value);

        return $query->first() ?? abort(404);
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => StatusTransaksi::class,
            'transaction_date' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get the ulasan (review) for this transaction.
     */
    public function ulasan(): HasOne
    {
        return $this->hasOne(Ulasan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    /**
     * Pembayaran yang berhasil melunasi tagihan transaksi.
     */
    public function successfulPayment(): ?Payment
    {
        return $this->payments
            ->whereIn('status', [StatusPembayaran::Paid, StatusPembayaran::Completed])
            ->sortByDesc('paid_at')
            ->first();
    }

    /**
     * Metode pembayaran aktual yang berhasil digunakan.
     */
    public function getActualPaymentMethodAttribute(): ?string
    {
        return $this->successfulPayment()?->payment_method
            ?? $this->payments->sortByDesc('id')->first()?->payment_method;
    }

    /**
     * Alias atribut payment_method sesuai pembayaran aktual.
     */
    public function getPaymentMethodAttribute(): ?string
    {
        return $this->actual_payment_method;
    }

    /**
     * Label representasi metode pembayaran aktual untuk UI.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        $payment = $this->successfulPayment() ?? $this->payments->sortByDesc('id')->first();
        if (! $payment) {
            return 'TUNAI';
        }

        $method = strtolower($payment->payment_method);
        $channel = $payment->channel ? strtoupper($payment->channel) : null;

        return match ($method) {
            'cash' => 'TUNAI',
            'qris' => 'QRIS',
            'transfer' => 'TRANSFER',
            'e_wallet', 'e-wallet' => $channel ? "E-WALLET ({$channel})" : 'E-WALLET',
            'virtual_account' => $channel ? "VA ({$channel})" : 'VIRTUAL ACCOUNT',
            'card', 'debit', 'credit' => 'KARTU',
            default => strtoupper(str_replace(['_', '-'], ' ', $method)),
        };
    }

    /**
     * Nama pelanggan dari sumber booking, order, atau fallback umum.
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->booking?->user?->name
            ?? $this->order?->user?->name
            ?? 'Pelanggan Umum';
    }

    /**
     * Nomor telepon pelanggan dari booking atau order jika tersedia.
     */
    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->booking?->user?->phone
            ?? $this->booking?->user?->phone_number
            ?? $this->order?->user?->phone
            ?? $this->order?->user?->phone_number
            ?? null;
    }

    /**
     * Periksa apakah transaksi memiliki nomor telepon pelanggan yang valid.
     */
    public function hasValidCustomerPhone(): bool
    {
        return WhatsAppService::isValidIndonesianPhone($this->customer_phone);
    }
}
