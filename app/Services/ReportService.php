<?php

namespace App\Services;

use App\Models\Defect;
use App\Models\Invoice;
use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\QualityCheck;
use App\Models\RawMaterial;
use App\Models\SalesOrder;

class ReportService
{
    public function dashboard(): array
    {
        return [
            'total_orders' => ProductionOrder::count(),
            'active_production' => ProductionBatch::where('status', 'running')->count(),
            'pending_quality' => QualityCheck::where('status', 'pending')->count(),
            'low_stock_materials' => RawMaterial::whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'recent_orders' => ProductionOrder::with('product')->latest()->take(5)->get(),
            'recent_sales' => SalesOrder::with('customer')->latest()->take(5)->get(),
        ];
    }

    public function production(array $filters = []): array
    {
        $service = app(ProductionService::class);
        return $service->getProductionReport($filters);
    }

    public function inventory(array $filters = []): array
    {
        $service = app(InventoryService::class);
        return $service->getInventoryReport($filters);
    }

    public function sales(array $filters = []): array
    {
        $service = app(SalesService::class);
        return $service->getSalesReport($filters);
    }

    public function quality(array $filters = []): array
    {
        $service = app(QualityService::class);
        return $service->getQualityReport($filters);
    }

    public function purchase(array $filters = []): array
    {
        $service = app(PurchaseService::class);
        return $service->getPurchaseReport($filters);
    }

    public function export(string $type, array $filters = []): array
    {
        return match ($type) {
            'production' => $this->production($filters),
            'inventory' => $this->inventory($filters),
            'sales' => $this->sales($filters),
            'quality' => $this->quality($filters),
            'purchase' => $this->purchase($filters),
            default => [],
        };
    }
}
