<?php

namespace App\Enums;

enum LoyaltyTransactionType: string
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Expired = 'expired';
    case Adjustment = 'adjustment';
}
