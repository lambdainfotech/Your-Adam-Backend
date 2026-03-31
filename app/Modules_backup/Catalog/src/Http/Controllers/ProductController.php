<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Controllers;

use App\Modules\Catalog\Contracts\ProductServiceInterface;
use App\Modules\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(private ProductServiceInterface $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->paginatedResponse($this->service->list($request->all()));
    }

    public function show(string $slug): JsonResponse
    {
        return $this->successResponse($this->service->getBySlug($slug));
    }

    public function search(Request $request): JsonResponse
    {
        return $this->paginatedResponse(
            $this->service->search($request->get('q', ''), $request->all())
        );
    }
}
