<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Contracts\CategoryServiceInterface;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Repositories\CategoryRepository;
use App\Modules\Shared\Services\BaseService;

class CategoryService extends BaseService implements CategoryServiceInterface
{
    public function __construct(private CategoryRepository $repository)
    {
    }

    public function getTree(): array
    {
        return $this->repository->getTree();
    }

    public function getBySlug(string $slug): Category
    {
        return $this->repository->findBySlug($slug);
    }
}
