<?php

namespace App\Enums;

enum InventoryStockStatus: string
{
    case Available = 'tersedia';
    case Low = 'menipis';
    case OutOfStock = 'habis';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Low => 'Menipis',
            self::OutOfStock => 'Habis',
        };
    }
}
