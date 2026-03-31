<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Enums;

enum AttributeType: string
{
    case TEXT = 'text';
    case COLOR = 'color';
    case SELECT = 'select';
    case NUMBER = 'number';
}
