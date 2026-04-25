<?php

declare(strict_types=1);

namespace App\Modules\Report\DTOs;

readonly class ReportFilterDTO
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $groupBy = null, // day, week, month
        public ?int $categoryId = null,
        public ?int $productId = null,
        public ?string $format = 'json', // json, excel
        public ?string $status = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            dateFrom: $data['date_from'] ?? now()->subDays(30)->toDateString(),
            dateTo: $data['date_to'] ?? now()->toDateString(),
            groupBy: $data['group_by'] ?? 'day',
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            format: $data['format'] ?? 'json',
            status: $data['status'] ?? null
        );
    }
}
