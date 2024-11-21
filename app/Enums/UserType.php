<?php

namespace App\Enums;

enum UserType: string
{
    case Manager = 'manager';
    case Employee = 'employee';
    case Author = 'author';
    case User = 'user';
    case Organ = 'organ';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
