<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function suppliers(): JsonResponse
    {
        $suppliers = Supplier::latest()->paginate(20);
        return response()->json($suppliers);
    }

    public function storeSupplier(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());
        return response()->json($supplier, 201);
    }

    public function showSupplier(Supplier $supplier): JsonResponse
    {
        $supplier->load('purchaseOrders');
        return response()->json($supplier);
    }

    public function updateSupplier(StoreSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());
        return response()->json($supplier);
    }

    public function destroySupplier(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted']);
    }

    public function orders(): JsonResponse
    {
        $orders = PurchaseOrder::with('supplier', 'items.material', 'creator')->latest()->paginate(20);
        return response()->json($orders);
    }

    public function storeOrder(StorePurchaseOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['items'] = $this->normalizeItems($data['items'] ?? []);
        $order = $this->purchaseService->createOrder($data);
        return response()->json($order->load('supplier', 'items.material'), 201);
    }

    public function showOrder(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load('supplier', 'items.material', 'goodsReceipts', 'purchaseInvoices', 'creator');
        return response()->json($purchaseOrder);
    }

    public function updateOrder(StorePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $data = $request->validated();
        $data['items'] = $this->normalizeItems($data['items'] ?? []);

        $purchaseOrder = \Illuminate\Support\Facades\DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => $data['status'] ?? $purchaseOrder->status,
                'notes' => $data['notes'] ?? null,
            ]);

            if (!empty($data['items'])) {
                $purchaseOrder->items()->delete();
                foreach ($data['items'] as $item) {
                    $purchaseOrder->items()->create($item);
                }
                $total = $purchaseOrder->items->sum('total');
                $purchaseOrder->update(['total_amount' => $total]);
            }

            return $purchaseOrder;
        });

        return response()->json($purchaseOrder->load('supplier', 'items.material'));
    }

    protected function normalizeItems(array $items): array
    {
        return array_map(function ($item) {
            $item['material_id'] = $item['material_id'] ?? ($item['raw_material_id'] ?? null);
            $item['total'] = $item['total'] ?? ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0));
            unset($item['raw_material_id']);
            return $item;
        }, $items);
    }

    public function destroyOrder(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->delete();
        return response()->json(['message' => 'Purchase order deleted']);
    }

    public function goodsReceipts(): JsonResponse
    {
        $receipts = \App\Models\GoodsReceipt::with('order.supplier', 'receiver')->latest()->paginate(20);
        return response()->json($receipts);
    }

    public function storeGoodsReceipt(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'raw_material_id' => 'nullable|exists:raw_materials,id',
            'quantity_received' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
        ]);

        $order = PurchaseOrder::findOrFail($data['purchase_order_id']);
        $receipt = $this->purchaseService->receiveGoods($order, [
            'receipt_date' => $data['received_date'] ?? now(),
        ]);

        if (!empty($data['raw_material_id']) && $data['quantity_received'] > 0) {
            $material = \App\Models\RawMaterial::find($data['raw_material_id']);
            app(\App\Services\InventoryService::class)->updateStock($material, 'in', $data['quantity_received']);
        }

        return response()->json($receipt->load('order.supplier', 'receiver'), 201);
    }

    public function receiveGoods(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $receipt = $this->purchaseService->receiveGoods($purchaseOrder, $request->validate([
            'receipt_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]));
        return response()->json($receipt, 201);
    }
}
