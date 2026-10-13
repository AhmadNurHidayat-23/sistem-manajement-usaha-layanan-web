<?php

namespace App\Enums;

enum StatusTransaksi: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * Label status transaksi.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Lunas',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }

    /**
     * Variant badge component.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending => 'warning',
            self::Cancelled, self::Refunded => 'danger',
        };
    }

    /**
     * CSS class badge.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Paid => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Cancelled, self::Refunded => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
