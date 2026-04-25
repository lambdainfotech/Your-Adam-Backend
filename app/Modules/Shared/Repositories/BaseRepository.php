<?php

declare(strict_types=1);

namespace App\Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function getCachePrefix(): string
    {
        return strtolower(class_basename($this->model));
    }
}
