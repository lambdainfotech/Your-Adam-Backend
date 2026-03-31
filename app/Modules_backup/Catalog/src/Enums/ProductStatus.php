<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Enums;

enum ProductStatus: int
{
    case DRAFT = 0;
    case ACTIVE = 1;
    case ARCHIVED = 2;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
        };
    }
}
