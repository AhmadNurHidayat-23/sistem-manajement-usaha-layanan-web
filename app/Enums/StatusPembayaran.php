<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Pending = 'pending';
    case Completed = 'completed'; // Legacy / cash payments
    case Paid = 'paid';           // Gateway confirmed paid
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    /**
     * Status final pembayaran.
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Paid, self::Completed, self::Failed, self::Expired, self::Refunded => true,
            default => false,
        };
    }

    /**
     * Human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Completed => 'Lunas',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
            self::Refunded => 'Dikembalikan',
        };
    }

    /**
     * Variant badge component.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Completed, self::Paid => 'success',
            self::Pending => 'warning',
            default => 'danger',
        };
    }

    /**
     * Badge CSS class.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Completed, self::Paid => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Failed => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Expired => 'bg-[#F5F8FC] text-[#667085] border-[#E4E7EC]',
            self::Refunded => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }
}
