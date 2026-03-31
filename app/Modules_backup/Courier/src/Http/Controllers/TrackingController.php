<?php

declare(strict_types=1);

namespace App\Modules\Courier\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Courier\Contracts\CourierServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        private CourierServiceInterface $courierService
    ) {
    }

    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required_without:tracking_number,mobile',
            'tracking_number' => 'required_without:order_id,mobile',
            'mobile' => 'required_without:order_id,tracking_number',
        ]);

        // Tracking logic based on provided params
        if ($request->has('order_id')) {
            $history = $this->courierService->getTrackingHistory($request->input('order_id'));

            return $this->successResponse([
                'history' => $history,
            ]);
        }

        if ($request->has('tracking_number')) {
            // Find by tracking number
            // Implementation depends on repository method
            return $this->successResponse([
                'message' => 'Tracking by tracking number',
                'tracking_number' => $request->input('tracking_number'),
            ]);
        }

        // Find by mobile number
        return $this->successResponse([
            'message' => 'Tracking by mobile number',
            'mobile' => $request->input('mobile'),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $history = $this->courierService->getTrackingHistory($id);

        return $this->successResponse([
            'history' => $history,
        ]);
    }
}
