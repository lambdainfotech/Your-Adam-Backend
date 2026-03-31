<?php

declare(strict_types=1);

namespace App\Modules\Report\Exports;

use App\Modules\Report\Enums\ReportType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromArray, WithHeadings, WithTitle
{
    public function __construct(
        private array $data,
        private ReportType $type
    ) {}

    public function array(): array
    {
        return $this->data['details']?->toArray() ?? [];
    }

    public function headings(): array
    {
        return match ($this->type) {
            ReportType::SALES => ['Period', 'Sales', 'Orders', 'Discounts'],
            ReportType::INVENTORY => ['ID', 'Product ID', 'SKU', 'Stock Quantity', 'Low Stock Alert'],
            ReportType::CUSTOMER => ['User ID', 'Orders', 'Total Spent'],
            ReportType::COUPON => ['Coupon ID', 'Usage Count', 'Total Discount'],
            default => ['Data'],
        };
    }

    public function title(): string
    {
        return $this->type->label();
    }
}
