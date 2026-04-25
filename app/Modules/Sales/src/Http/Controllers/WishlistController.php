<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Contracts\WishlistServiceInterface;
use App\Modules\Sales\Http\Requests\AddToWishlistRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $wishlist = $this->service->get($request->user()->id);

        return $this->paginatedResponse($wishlist);
    }

    public function store(AddToWishlistRequest $request): JsonResponse
    {
        $item = $this->service->add(
            $request->user()->id,
            $request->validated()['product_id']
        );

        return $this->createdResponse($item);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $this->service->remove($request->user()->id, $productId);

        return $this->noContentResponse();
    }

    public function check(Request $request, int $productId): JsonResponse
    {
        $isInWishlist = $this->service->isInWishlist(
            $request->user()->id,
            $productId
        );

        return $this->successResponse(['in_wishlist' => $isInWishlist]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $added = $this->service->toggle(
            $request->user()->id,
            $request->input('product_id')
        );

        return $this->successResponse([
            'in_wishlist' => $added,
            'message' => $added ? 'Added to wishlist' : 'Removed from wishlist',
        ]);
    }
}
