<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Contracts;

use App\Modules\Catalog\Models\Category;

interface CategoryServiceInterface
{
    public function getTree(): array;

    public function getBySlug(string $slug): Category;
}
