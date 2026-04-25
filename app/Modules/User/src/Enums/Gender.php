<?php

declare(strict_types=1);

namespace App\Modules\User\Enums;

enum Gender: int
{
    case MALE = 0;
    case FEMALE = 1;
    case OTHER = 2;
}
