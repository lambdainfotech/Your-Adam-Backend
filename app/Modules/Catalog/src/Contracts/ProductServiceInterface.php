<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Contracts;

use App\Modules\Catalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function list(array $filters): LengthAwarePaginator;

    public function getBySlug(string $slug): Product;

    public function search(string $query, array $filters): LengthAwarePaginator;
}
