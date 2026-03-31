<?php

declare(strict_types=1);

namespace App\Modules\User\Enums;

enum AddressType: string
{
    case HOME = 'home';
    case OFFICE = 'office';
    case OTHER = 'other';
}
