<?php

namespace App\Services;

use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\ProductionTarget;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    public function createOrder(array $data): ProductionOrder
    {
        $data['order_number'] = $data['order_number'] ?? 'PO-' . now()->format('YmdHis');
        $data['status'] = $data['status'] ?? 'draft';

        return ProductionOrder::create($data);
    }

    public function updateOrder(ProductionOrder $order, array $data): ProductionOrder
    {
        $order->update($data);
        return $order->fresh();
    }

    public function startBatch(array $data): ProductionBatch
    {
        $data['start_time'] = now();
        $data['status'] = 'running';

        $batch = ProductionBatch::create($data);

        $batch->order->update(['status' => 'in_progress']);

        return $batch->load(['order.product', 'shift', 'machine', 'operator']);
    }

    public function completeBatch(ProductionBatch $batch, array $data): ProductionBatch
    {
        $data['end_time'] = now();
        $data['status'] = 'completed';
        $batch->update($data);

        $order = $batch->order;
        $totalProduced = $order->batches()->where('status', 'completed')->sum('quantity_produced');

        if ($totalProduced >= $order->quantity) {
            $order->update(['status' => 'completed']);
        }

        return $batch->fresh();
    }

    public function getProductionReport(array $filters = []): array
    {
        $query = ProductionBatch::with(['order.product', 'shift', 'machine']);

        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $batches = $query->get();

        return [
            'total_produced' => $batches->sum('quantity_produced'),
            'total_rejected' => $batches->sum('quantity_rejected'),
            'batches_count' => $batches->count(),
            'batches' => $batches,
        ];
    }
}
