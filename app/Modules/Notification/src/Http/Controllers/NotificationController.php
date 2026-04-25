<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Contracts\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationServiceInterface $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->paginatedResponse(
            $this->service->getForUser($request->user()->id)
        );
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $this->service->markAsRead($id, $request->user()->id);

        return $this->successResponse();
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->successResponse([
            'count' => $this->service->getUnreadCount($request->user()->id),
        ]);
    }
}
