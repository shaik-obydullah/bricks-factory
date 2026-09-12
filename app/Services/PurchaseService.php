<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function createOrder(array $data): PurchaseOrder
    {
        $data['order_number'] = $data['order_number'] ?? 'PO-' . now()->format('YmdHis');
        $data['status'] = $data['status'] ?? 'draft';
        $data['total_amount'] = $data['total_amount'] ?? 0;

        return DB::transaction(function () use ($data) {
            $order = PurchaseOrder::create($data);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }

                $total = $order->items->sum('total');
                $order->update(['total_amount' => $total]);
            }

            return $order->fresh();
        });
    }

    public function receiveGoods(PurchaseOrder $order, array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($order, $data) {
            $receipt = GoodsReceipt::create([
                'order_id' => $order->id,
                'receipt_date' => $data['receipt_date'] ?? now(),
                'received_by' => $data['received_by'] ?? auth()->id(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($order->items as $item) {
                $item->material->increment('current_stock', $item->quantity);
            }

            $order->update(['status' => 'received']);

            return $receipt;
        });
    }

    public function getPurchaseReport(array $filters = []): array
    {
        $query = PurchaseOrder::with(['supplier', 'items.material']);

        if (!empty($filters['from'])) {
            $query->where('order_date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('order_date', '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->get();

        return [
            'total_orders' => $orders->count(),
            'total_amount' => $orders->sum('total_amount'),
            'orders' => $orders,
        ];
    }
}
