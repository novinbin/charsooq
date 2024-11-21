<?php

namespace App\Enums;

enum PaymentType: string
{
    case SingleInstallment = 'SingleInstallment';
    case AllInstallments = 'AllInstallments';
    case Factor = 'factor';
    case Balance = 'balance';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
