<?php

namespace App\Enums;

enum StatusPesanan: string
{
    case Pending = 'pending';
    case Received = 'received';
    case Processing = 'processing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Label status pesanan drop-off dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Received => 'Diterima',
            self::Processing => 'Diproses',
            self::Ready => 'Siap Diambil',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Tailwind badge class untuk status pesanan.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-700',
            self::Received => 'bg-blue-100 text-blue-700',
            self::Processing => 'bg-indigo-100 text-indigo-700',
            self::Ready => 'bg-teal-100 text-teal-700',
            self::Completed => 'bg-emerald-100 text-emerald-700',
            self::Cancelled => 'bg-rose-100 text-rose-700',
        };
    }
}
