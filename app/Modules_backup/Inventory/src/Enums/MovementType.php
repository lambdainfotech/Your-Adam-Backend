<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum MovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUSTMENT = 'adjustment';
}
