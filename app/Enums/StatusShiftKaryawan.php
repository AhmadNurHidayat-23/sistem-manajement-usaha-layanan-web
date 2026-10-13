<?php

namespace App\Enums;

enum StatusShiftKaryawan: string
{
    case Scheduled = 'scheduled';
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Off = 'off';

    /**
     * Label status shift staf dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Present => 'Hadir',
            self::Late => 'Terlambat',
            self::Absent => 'Absen',
            self::Off => 'Libur',
        };
    }
}
