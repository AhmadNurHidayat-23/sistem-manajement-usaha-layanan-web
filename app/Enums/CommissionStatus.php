<?php

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    /**
     * Label status komisi dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Paid => 'Dibayar',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
