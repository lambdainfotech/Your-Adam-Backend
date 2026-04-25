<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Repositories;

use App\Modules\Catalog\Models\Category;
use App\Modules\Shared\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'categories';
    }

    public function getTree(): array
    {
        return $this->model->with('children')->root()->active()->get()->toArray();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }
}
