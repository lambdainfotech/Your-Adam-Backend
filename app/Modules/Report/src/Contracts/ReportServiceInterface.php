<?php

declare(strict_types=1);

namespace App\Modules\Report\Contracts;

use App\Modules\Report\DTOs\ReportFilterDTO;
use App\Modules\Report\Enums\ReportType;

interface ReportServiceInterface
{
    public function generateSalesReport(ReportFilterDTO $filters): array;
    
    public function generateInventoryReport(): array;
    
    public function generateCustomerReport(ReportFilterDTO $filters): array;
    
    public function generateCouponReport(ReportFilterDTO $filters): array;
    
    public function exportToExcel(ReportType $type, ReportFilterDTO $filters): string;
    
    public function getDashboardStats(): array;
}
