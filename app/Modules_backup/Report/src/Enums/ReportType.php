<?php

declare(strict_types=1);

namespace App\Modules\Report\Enums;

enum ReportType: string
{
    case SALES = 'sales';
    case INVENTORY = 'inventory';
    case CUSTOMER = 'customer';
    case COUPON = 'coupon';
    case PRODUCT = 'product';

    public function label(): string
    {
        return match ($this) {
            self::SALES => 'Sales Report',
            self::INVENTORY => 'Inventory Report',
            self::CUSTOMER => 'Customer Report',
            self::COUPON => 'Coupon Usage Report',
            self::PRODUCT => 'Product Performance Report',
        };
    }
}
