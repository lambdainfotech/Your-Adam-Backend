<?php

declare(strict_types=1);

namespace App\Modules\Core\Enums;

enum OrderDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
