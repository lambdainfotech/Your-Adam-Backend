<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ShippingCalculatorService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    use ApiResponse;
    private ShippingCalculatorService $shippingService;

    public function __construct(ShippingCalculatorService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Calculate shipping cost
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'nullable|numeric',
            'city' => 'nullable|string',
            'address_id' => 'nullable|integer',
        ]);

        $result = $this->shippingService->calculateShipping(
            $request->get('items'),
            $request->get('address_id'),
            $request->get('city')
        );

        return $this->success($result, 'Shipping cost calculated successfully');
    }

    /**
     * Get available shipping methods
     */
    public function methods(): JsonResponse
    {
        $methods = $this->shippingService->getShippingMethods();

        return $this->success($methods, 'Shipping methods retrieved successfully');
    }
}
