<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum UserStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case SUSPENDED = 2;

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }
}
