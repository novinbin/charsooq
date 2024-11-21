<?php

namespace App\Enums;

enum DelayStatus: string
{
    case Clean = "clean";
    case HasDelay = "has_delay";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
