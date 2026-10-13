<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Cashier = 'cashier';
    case Employee = 'employee';
    case Customer = 'customer';
}
