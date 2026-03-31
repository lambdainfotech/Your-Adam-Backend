<?php

declare(strict_types=1);

namespace App\Modules\Core\Enums;

enum ResponseStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}
