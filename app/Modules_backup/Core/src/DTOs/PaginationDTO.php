<?php

declare(strict_types=1);

namespace App\Modules\Core\DTOs;

readonly class PaginationDTO
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public ?string $sortBy = null,
        public ?string $sortOrder = 'asc'
    ) {}

    /**
     * Create from request data.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 20),
            sortBy: $data['sort_by'] ?? null,
            sortOrder: $data['sort_order'] ?? 'asc'
        );
    }
}
