<?php

namespace App\Enums;

enum CashierShiftStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * Label status shift kasir dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Buka',
            self::Closed => 'Tutup',
        };
    }
}
