<?php

namespace App\Services;

use App\Models\Defect;
use App\Models\Invoice;
use App\Models\Machine;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\QualityCheck;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;

class ReportService
{
    public function dashboard(): array
    {
        $lowStock = RawMaterial::whereColumn('current_stock', '<=', 'minimum_stock')->count();

        return [
            'total_orders' => ProductionOrder::count(),
            'total_products' => Product::count(),
            'active_batches' => ProductionBatch::where('status', 'running')->count(),
            'active_production' => ProductionBatch::where('status', 'running')->count(),
            'pending_quality' => QualityCheck::where('status', 'pending')->count(),
            'low_stock_materials' => $lowStock,
            'low_stock_items' => $lowStock,
            'pending_orders' => ProductionOrder::whereIn('status', ['draft', 'confirmed'])->count(),
            'in_progress_orders' => ProductionOrder::where('status', 'in_progress')->count(),
            'completed_today' => ProductionOrder::where('status', 'completed')
                ->whereDate('updated_at', now()->toDateString())
                ->count(),
            'active_machines' => Machine::where('status', 'active')->count(),
            'total_machines' => Machine::count(),
            'total_raw_materials' => RawMaterial::count(),
            'stock_movements_today' => StockMovement::whereDate('created_at', now()->toDateString())->count(),
            'total_warehouses' => Warehouse::count(),
            'recent_orders' => ProductionOrder::with('product')->latest()->take(5)->get(),
            'recent_sales' => SalesOrder::with('customer')->latest()->take(5)->get(),
            'recent_activity' => $this->recentActivity(),
            'monthly_production' => $this->monthlyProduction(),
        ];
    }

    protected function monthlyProduction(): array
    {
        $monthly = [];

        foreach (range(5, 0) as $offset) {
            $date = now()->startOfMonth()->subMonths($offset);
            $monthly[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'total' => 0,
            ];
        }

        $rows = ProductionBatch::query()
            ->whereNotNull('start_time')
            ->where('start_time', '>=', now()->startOfMonth()->subMonths(5)->toDateTimeString())
            ->selectRaw("DATE_FORMAT(start_time, '%Y-%m') as month, SUM(quantity_produced) as total")
            ->groupBy('month')
            ->get();

        foreach ($rows as $row) {
            $key = array_search($row->month, array_column($monthly, 'month'), true);
            if ($key !== false) {
                $monthly[$key]['total'] = (int) $row->total;
            }
        }

        return $monthly;
    }

    protected function humanizeStatus(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    protected function recentActivity(): array
    {
        $activity = [];

        foreach (ProductionOrder::with('product')->latest()->take(5)->get() as $order) {
            $activity[] = [
                'id' => 'po-' . $order->id,
                'type' => 'production',
                'description' => 'Production order ' . $order->order_number
                    . ($order->product ? ' for ' . $order->product->name : '')
                    . ' (' . $this->humanizeStatus($order->status) . ')',
                'created_at' => $order->created_at?->toDateTimeString(),
            ];
        }

        foreach (SalesOrder::with('customer')->latest()->take(5)->get() as $order) {
            $activity[] = [
                'id' => 'so-' . $order->id,
                'type' => 'sales',
                'description' => 'Sales order ' . $order->order_number
                    . ($order->customer ? ' from ' . $order->customer->name : '')
                    . ' (' . $this->humanizeStatus($order->status) . ')',
                'created_at' => $order->created_at?->toDateTimeString(),
            ];
        }

        foreach (QualityCheck::latest()->take(5)->get() as $check) {
            $activity[] = [
                'id' => 'qc-' . $check->id,
                'type' => 'quality',
                'description' => 'Quality check #' . $check->id . ' (' . $this->humanizeStatus($check->status) . ')',
                'created_at' => $check->created_at?->toDateTimeString(),
            ];
        }

        foreach (StockMovement::latest()->take(5)->get() as $movement) {
            $activity[] = [
                'id' => 'sm-' . $movement->id,
                'type' => 'inventory',
                'description' => 'Stock movement (' . $this->humanizeStatus($movement->movement_type) . ') ref ' . $movement->reference,
                'created_at' => $movement->created_at?->toDateTimeString(),
            ];
        }

        usort($activity, fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));

        return array_slice($activity, 0, 10);
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
