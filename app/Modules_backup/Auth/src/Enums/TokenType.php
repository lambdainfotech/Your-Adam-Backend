<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum TokenType: string
{
    case ACCESS = 'access';
    case REFRESH = 'refresh';
}
