<?php

namespace App\Enums;

enum PromotionStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';

    /**
     * Label status promo dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Pending => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
