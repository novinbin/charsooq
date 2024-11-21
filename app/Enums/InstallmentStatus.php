<?php

namespace App\Enums;

enum InstallmentStatus: string
{
    case InDue = "in_due";
    case Paid = "paid";
    case Delayed = "delayed";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
