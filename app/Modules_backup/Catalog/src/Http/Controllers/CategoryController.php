<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Controllers;

use App\Modules\Catalog\Contracts\CategoryServiceInterface;
use App\Modules\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseController
{
    public function __construct(private CategoryServiceInterface $service)
    {
    }

    public function index(): JsonResponse
    {
        return $this->successResponse($this->service->getTree());
    }

    public function show(string $slug): JsonResponse
    {
        return $this->successResponse($this->service->getBySlug($slug));
    }
}
