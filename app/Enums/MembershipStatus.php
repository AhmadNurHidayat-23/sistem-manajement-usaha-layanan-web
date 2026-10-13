<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /**
     * Label status keanggotaan dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::Suspended => 'Ditangguhkan',
        };
    }
}
