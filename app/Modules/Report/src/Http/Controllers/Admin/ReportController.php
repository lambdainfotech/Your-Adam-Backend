<?php

declare(strict_types=1);

namespace App\Modules\Report\Http\Controllers\Admin;

use App\Modules\Core\Abstracts\BaseController;
use App\Modules\Report\Contracts\ReportServiceInterface;
use App\Modules\Report\DTOs\ReportFilterDTO;
use App\Modules\Report\Enums\ReportType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function __construct(
        private ReportServiceInterface $reportService
    ) {}

    public function dashboard(): JsonResponse
    {
        return $this->successResponse($this->reportService->getDashboardStats());
    }

    public function sales(Request $request): JsonResponse
    {
        $filters = ReportFilterDTO::fromRequest($request->all());
        return $this->successResponse($this->reportService->generateSalesReport($filters));
    }

    public function inventory(): JsonResponse
    {
        return $this->successResponse($this->reportService->generateInventoryReport());
    }

    public function customers(Request $request): JsonResponse
    {
        $filters = ReportFilterDTO::fromRequest($request->all());
        return $this->successResponse($this->reportService->generateCustomerReport($filters));
    }

    public function coupons(Request $request): JsonResponse
    {
        $filters = ReportFilterDTO::fromRequest($request->all());
        return $this->successResponse($this->reportService->generateCouponReport($filters));
    }

    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:sales,inventory,customer,coupon',
            'format' => 'nullable|in:excel',
        ]);

        $filters = ReportFilterDTO::fromRequest($request->all());
        $type = ReportType::from($request->input('type'));
        
        $filename = $this->reportService->exportToExcel($type, $filters);
        
        return $this->successResponse([
            'filename' => $filename,
            'download_url' => asset("storage/reports/{$filename}"),
        ], 'Report generated successfully');
    }
}
