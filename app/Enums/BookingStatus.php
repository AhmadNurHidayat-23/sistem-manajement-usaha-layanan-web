<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Label status booking.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Confirmed => 'Dikonfirmasi',
            self::InProgress => 'Diproses',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Tailwind badge class untuk status booking.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-700',
            self::Confirmed => 'bg-blue-100 text-blue-700',
            self::InProgress => 'bg-indigo-100 text-indigo-700',
            self::Completed => 'bg-emerald-100 text-emerald-700',
            self::Cancelled => 'bg-rose-100 text-rose-700',
        };
    }
}
