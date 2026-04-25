<?php

declare(strict_types=1);

namespace App\Modules\Sales\Enums;

enum PaymentMethod: string
{
    case COD = 'cod';
    case ONLINE = 'online';

    public function label(): string
    {
        return match ($this) {
            self::COD => 'Cash on Delivery',
            self::ONLINE => 'Online Payment',
        };
    }
}
